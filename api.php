<?php
/**
 * Penangan API untuk Pemrosesan Pembayaran
 * File ini menangani berbagai operasi terkait pembayaran termasuk:
 * - Daftar produk
 * - Pembuatan transaksi
 * - Pengecekan status pembayaran
 * - Pembatalan transaksi
 */

session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-BiPEZ8YxMZheywHq49sAQthl';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Atur header response ke JSON
header('Content-Type: application/json');

$request_method = $_SERVER["REQUEST_METHOD"];

/**
 * Penangan Utama Request
 * Mengarahkan request ke fungsi yang sesuai berdasarkan metode HTTP dan action
 */
try {
    switch ($request_method) {
        case 'GET':
            get_products();
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['action'])) {
                throw new Exception("Action tidak ditemukan dalam request");
            }

            switch ($data['action']) {
                case 'create_transaction':
                    create_transaction($data);
                    break;
                case 'check_payment_status':
                    check_payment_status($data);
                    break;
                case 'cancel_transaction':
                    cancel_transaction($data);
                    break;
                case 'create_free_transaction':
                    create_free_transaction($data);
                    break;
                default:
                    throw new Exception("Action tidak valid");
            }
            break;
        default:
            throw new Exception("Metode request tidak valid");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

/**
 * Mengambil semua produk dari database
 * @return void Output JSON dari semua produk
 */
function get_products() {
    global $db;
    try {
        $query = "SELECT * FROM products";
        $statement = $db->prepare($query);
        $statement->execute();
        $products = $statement->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

/**
 * Membuat transaksi baru
 * @param array $data Data transaksi yang diperlukan
 * @return void Output JSON dengan detail transaksi atau pesan error
 */
function create_transaction($data) {
    global $db;

    // Validasi data yang diperlukan
    if (!isset($data['product_id']) || !isset($data['product_name']) || !isset($data['product_price'])) {
        throw new Exception("Data produk tidak lengkap");
    }

    try {
        // Persiapkan data transaksi
        $order_id = 'TRX-' . time() . '-' . uniqid();
        $product_id = $data['product_id'];
        $product_name = $data['product_name'];
        $product_price = intval($data['product_price']);
        $discount = isset($data['discount']) ? intval($data['discount']) : 0;

        // Validasi harga produk dan diskon
        if ($product_price < 0 || $discount < 0) {
            throw new Exception("Harga produk dan diskon harus berupa angka positif");
        }

        // Hitung total harga
        $total_price = max(0, $product_price - $discount); // Mengizinkan total_price menjadi 0

        // Simpan transaksi ke database
        $stmt = $db->prepare("INSERT INTO transaksi (order_id, product_id, product_name, price, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$order_id, $product_id, $product_name, $total_price]);

        // Jika total_price adalah 0, langsung arahkan ke halaman sukses
        if ($total_price == 0) {
            echo json_encode([
                'success' => true,
                'redirect' => 'transberhasil.php' // Kembalikan URL untuk pengalihan
            ]);
            return; // Keluar dari fungsi
        }

        // Siapkan parameter Midtrans
        $transaction_params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => intval($total_price),
                'currency' => 'IDR'
            ],
            'item_details' => [[
                'id' => $product_id,
                'price' => intval($total_price),
                'quantity' => 1,
                'name' => $product_name
            ]],
            'customer_details' => [
                'first_name' => "Pembeli",
                'last_name' => "Satu",
                'email' => "pembeli@example.com",
                'phone' => "081234567890"
            ]
        ];

        // Proses pembayaran dengan Midtrans
        $qris_response = \Midtrans\CoreApi::charge($transaction_params);
        
        // Ambil URL QR Code
        $qr_code_url = null;
        if (isset($qris_response->actions)) {
            foreach ($qris_response->actions as $action) {
                if ($action->name == 'generate-qr-code') {
                    $qr_code_url = $action->url;
                    break;
                }
            }
        }

        if (!$qr_code_url) {
            throw new Exception("URL QR Code tidak ditemukan");
        }

        echo json_encode([
            'success' => true,
            'qr_code_url' => $qr_code_url,
            'order_id' => $order_id
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Memeriksa status pembayaran
 * @param array $data Data dengan transaction_id
 * @return void Output JSON dengan status pembayaran
 */
function check_payment_status($data) {
    global $db;

    if (!isset($data['transaction_id'])) {
        throw new Exception("ID transaksi tidak ditemukan");
    }

    try {
        $status = \Midtrans\Transaction::status($data['transaction_id']);
        
        // Ambil status transaksi
        $transaction_status = null;
        if (is_object($status)) {
            $transaction_status = $status->transaction_status;
        } elseif (is_array($status)) {
            $transaction_status = $status['transaction_status'] ?? null;
        }

        if (!$transaction_status) {
            throw new Exception("Status transaksi tidak ditemukan");
        }

        // Update status di database
        $stmt = $db->prepare("UPDATE transaksi SET status = ? WHERE order_id = ?");
        $stmt->execute([$transaction_status, $data['transaction_id']]);

        // Simpan ke session jika pembayaran berhasil
        if ($transaction_status === 'settlement') {
            $stmt = $db->prepare("SELECT * FROM transaksi WHERE order_id = ?");
            $stmt->execute([$data['transaction_id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($transaction) {
                $_SESSION['successful_transaction'] = [
                    'transaction_id' => $transaction['order_id'],
                    'product_name' => $transaction['product_name'],
                    'amount' => $transaction['price'],
                    'created_at' => $transaction['created_at']
                ];
            }
        }

        echo json_encode([
            "success" => true,
            "status" => $transaction_status,
            "redirect" => ($transaction_status === 'settlement') ? 'transberhasil.php' : null
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }
}

/**
 * Membatalkan transaksi
 * @param array $data Data dengan transaction_id
 * @return void Output JSON dengan status pembatalan
 */
function cancel_transaction($data) {
    global $db;

    if (!isset($data['transaction_id'])) {
        throw new Exception("ID transaksi tidak ditemukan");
    }

    try {
        // Update status transaksi menjadi 'cancelled'
        $stmt = $db->prepare("UPDATE transaksi SET status = 'cancelled' WHERE order_id = ?");
        $stmt->execute([$data['transaction_id']]);

        // Simpan data pembatalan ke session
        $stmt = $db->prepare("SELECT * FROM transaksi WHERE order_id = ?");
        $stmt->execute([$data['transaction_id']]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            $_SESSION['cancelled_transaction'] = [
                'transaction_id' => $transaction['order_id'],
                'product_name' => $transaction['product_name'],
                'amount' => $transaction['price'],
                'created_at' => $transaction['created_at']
            ];
        }

        echo json_encode([
            "success" => true,
            "message" => "Transaksi berhasil dibatalkan"
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }
}

/**
 * Menangani notifikasi dari Midtrans
 * @return void Output JSON dengan status pemrosesan notifikasi
 */
function midtrans_notification() {
    global $db;

    try {
        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $order_id = $notif->order_id;

        if ($transaction == 'settlement') {
            // Update status transaksi menjadi 'settlement'
            $stmt = $db->prepare("UPDATE transaksi SET status = 'settlement' WHERE order_id = ?");
            $stmt->execute([$order_id]);
        } else if ($transaction == 'cancel') {
            // Update status transaksi menjadi 'cancelled'
            $stmt = $db->prepare("UPDATE transaksi SET status = 'cancelled' WHERE order_id = ?");
            $stmt->execute([$order_id]);
        } else if ($transaction == 'expire') {
            // Update status transaksi menjadi 'expire' hanya jika status sebelumnya bukan 'cancelled'
            $stmt = $db->prepare("UPDATE transaksi SET status = 'expire' WHERE order_id = ? AND status != 'cancelled'");
            $stmt->execute([$order_id]);
        }

        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

/**
 * Membuat transaksi gratis
 * @param array $data Data transaksi yang diperlukan
 * @return void Output JSON dengan detail transaksi atau pesan error
 */
function create_free_transaction($data) {
    global $db;

    if (!isset($data['product_id']) || !isset($data['product_name'])) {
        throw new Exception("Data produk tidak lengkap");
    }

    try {
        $order_id = 'FREE-' . time() . '-' . uniqid();
        $product_id = $data['product_id'];
        $product_name = $data['product_name'];

        // Simpan transaksi ke database dengan status 'settlement'
        // Biarkan created_at diisi otomatis oleh MySQL
        $stmt = $db->prepare("INSERT INTO transaksi (order_id, product_id, product_name, price, status) VALUES (?, ?, ?, 0, 'settlement')");
        $stmt->execute([$order_id, $product_id, $product_name]);

        // Ambil data transaksi yang baru saja dibuat, termasuk created_at dari database
        $stmt = $db->prepare("SELECT * FROM transaksi WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        // Simpan ke session menggunakan created_at dari database
        $_SESSION['successful_transaction'] = [
            'transaction_id' => $order_id,
            'product_name' => $product_name,
            'amount' => 0,
            'created_at' => $transaction['created_at']  // Menggunakan timestamp dari database
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Transaksi gratis berhasil diproses',
            'order_id' => $order_id,
            'redirect' => 'transberhasil.php'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

if (isset($_SESSION['pending_one_time_voucher'])) {
    $voucherCode = $_SESSION['pending_one_time_voucher'];
    
    // Update status voucher
    date_default_timezone_set('Asia/Jakarta');
    $currentDateTime = date('Y-m-d H:i:s');
    
    $updateStmt = $conn->prepare("UPDATE vouchers2 SET used_at = ? WHERE code = ? AND one_time_use = 1");
    $updateStmt->bind_param("ss", $currentDateTime, $voucherCode);
    $updateStmt->execute();
    
    // Bersihkan session
    unset($_SESSION['pending_one_time_voucher']);
    unset($_SESSION['active_voucher']);
}
// Setelah pembayaran berhasil
if (isset($_SESSION['active_voucher'])) {
    $voucherCode = $_SESSION['active_voucher'];
    
    // Update status voucher untuk voucher sekali pakai
    $stmt = $conn->prepare("SELECT one_time_use FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucherData = $result->fetch_assoc();
    
    if ($voucherData && $voucherData['one_time_use'] == 1) {
        date_default_timezone_set('Asia/Jakarta');
        $currentDateTime = date('Y-m-d H:i:s');
        
        $updateStmt = $conn->prepare("UPDATE vouchers2 SET used_at = ? WHERE code = ?");
        $updateStmt->bind_param("ss", $currentDateTime, $voucherCode);
        $updateStmt->execute();
    }
    
    // Hapus session voucher
    unset($_SESSION['active_voucher']);
    unset($_SESSION['lastUsedDiscount']);
}