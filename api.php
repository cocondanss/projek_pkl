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

        // Hitung total harga
        $total_price = max(0, $product_price - $discount); // Mengizinkan total_price menjadi 0

        // Simpan transaksi ke database
        $stmt = $db->prepare("INSERT INTO transaksi (order_id, product_id, product_name, price, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$order_id, $product_id, $product_name, $total_price]);

        // Jika total harga adalah 0, langsung set status menjadi 'settlement'
        if ($total_price == 0) {
            // Update status transaksi menjadi 'settlement' di database
            $stmt = $db->prepare("UPDATE transaksi SET status = 'settlement' WHERE order_id = ?");
            $stmt->execute([$order_id]);

            // Simpan data transaksi ke session
            $_SESSION['successful_transaction'] = [
                'transaction_id' => $order_id,
                'product_name' => $product_name,
                'amount' => $total_price,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Kembalikan respons untuk redirect
            return [
                'success' => true,
                'redirect' => 'transberhasil.php' // Redirect ke halaman transaksi berhasil
            ];
        }

        // Siapkan parameter Midtrans untuk pemrosesan pembayaran
        // ... (kode pemrosesan pembayaran)

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
        
        // Update status transaksi berdasarkan notifikasi
        $stmt = $db->prepare("UPDATE transaksi SET status = ? WHERE order_id = ?");
        $stmt->execute([$notif->transaction_status, $notif->order_id]);

        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function apply_voucher($data) {
    global $db;

    if (!isset($data['product_id']) || !isset($data['voucher_code']) || !isset($data['product_price'])) {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $product_id = $data['product_id'];
    $voucher_code = $data['voucher_code'];
    $product_price = $data['product_price'];

    try {
        $stmt = $db->prepare("SELECT id, discount_amount, is_free FROM vouchers2 WHERE code = ?");
        $stmt->execute([$voucher_code]);
        $voucher = $stmt->fetch();

        if ($voucher) {
            $discounted_price = $product_price; // Default to original price

            if ($voucher['is_free'] == 1) {
                // Jika voucher gratis, set harga menjadi 0
                $discounted_price = 0;
                $voucher_message = "Voucher gratis berhasil diterapkan!";
            } else {
                // Hitung diskon berdasarkan discount_amount
                $discountAmount = $voucher['discount_amount'];

                if ($discountAmount <= 100) {
                    // Diskon persentase
                    $discounted_price = $product_price - ($product_price * ($discountAmount / 100));
                } else {
                    // Diskon nominal langsung
                    $discounted_price = $product_price - $discountAmount;
                }

                $voucher_message = "Voucher berhasil diterapkan!";
            }

            // Pastikan harga tidak negatif
            $discounted_price = max($discounted_price, 0);

            echo json_encode([
                'success' => true,
                'discounted_price' => $discounted_price,
                'message' => $voucher_message
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Kode voucher tidak valid!"
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}