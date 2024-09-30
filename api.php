<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-BiPEZ8YxMZheywHq49sAQthl';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

header('Content-Type: application/json');

$request_method = $_SERVER["REQUEST_METHOD"];

try {
    switch ($request_method) {
        case 'GET':
            get_products();
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['action'])) {
                if ($data['action'] === 'apply_voucher') {
                    apply_voucher($data);
                } elseif ($data['action'] === 'create_transaction') {
                    create_transaction($data);
                }
            } else {
                header("HTTP/1.0 400 Bad Request");
                echo json_encode(["error" => "Missing action in request"]);
            }
            break;
        default:
            header("HTTP/1.0 405 Method Not Allowed");
            echo json_encode(["error" => "Invalid request method"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

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
        $stmt = $db->prepare("SELECT id, discount_amount, is_used FROM vouchers WHERE code = ?");
        $stmt->execute([$voucher_code]);
        $voucher = $stmt->fetch();

        if ($voucher) {
            if ($voucher['is_used'] == 0) {
                $discount = intval($voucher['discount_amount']);
                $discounted_price = $product_price - $discount;
                $voucher_message = "Voucher berhasil diterapkan!";
                
                // STORE THE CODE HERE
                date_default_timezone_set('Asia/Jakarta');
                $currentTime = date("Y-m-d H:i:s");
                $stmt = $db->prepare("UPDATE vouchers SET is_used = 1, used_at = ? WHERE id = ?");
                $stmt->execute([$currentTime, $voucher['id']]);

                echo json_encode([
                    'success' => true,
                    'discount' => $discount,
                    'discounted_price' => $discounted_price,
                    'message' => $voucher_message
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Kode voucher sudah digunakan!"
                ]);
            }
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
function create_transaction($data) {
    global $db;

    if (!isset($data['product_id']) || !isset($data['product_name']) || !isset($data['product_price'])) {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $product_id = $data['product_id'];
    $product_name = $data['product_name'];
    $product_price = intval($data['product_price']);
    $discount = isset($data['discount']) ? intval($data['discount']) : 0;
    $total_price = $product_price - $discount;

    // Periksa stok produk
    $stmt = $db->prepare("SELECT stok FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $stok = $stmt->fetchColumn();

    if ($stok <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Stok produk habis'
        ]);
        return;
    }

    // Kurangi stok produk
    $stmt = $db->prepare("UPDATE products SET stok = stok - 1 WHERE id = ?");
    $stmt->execute([$product_id]);

    if ($total_price < 0) {
        $total_price = 0;
    }

    $order_id = time();

    $transaction_details = array(
        'order_id' => $order_id,
        'gross_amount' => $total_price,
    );

    $item_details = array(
        array(
            'id' => $product_id,
            'price' => $product_price,
            'quantity' => 1,
            'name' => $product_name
        )
    );

    $customer_details = array(
        'first_name' => "Pembeli",
        'last_name' => "Satu",
        'email' => "pembeli@example.com",
        'phone' => "081234567890",
    );

    $transaction = array(
        'transaction_details' => $transaction_details,
        'item_details' => $item_details,
        'customer_details' => $customer_details,
        'enabled_payments' => array('other_qris'),
    );

    try {
        $snap_token = \Midtrans\Snap::getSnapToken($transaction);
        $snap_url = \Midtrans\Snap::createTransaction($transaction)->redirect_url;

        $stmt = $db->prepare("INSERT INTO transaksi (order_id, product_id, product_name, price, tanggal, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $product_id, $product_name, $total_price, date("Y-m-d"), 'pending']);

        echo json_encode([
            'success' => true,
            'snap_token' => $snap_token,
            'snap_url' => $snap_url
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Endpoint untuk menerima notifikasi dari Midtrans
// Endpoint untuk menerima notifikasi dari Midtrans
function midtrans_notification() {
    global $db;

    $notification = new \Midtrans\Notification();

    error_log("Received notification: " . json_encode($notification));

    $order_id = $notification->order_id;
    $transaction_status = $notification->transaction_status;
    $fraud_status = $notification->fraud_status;

    error_log("Transaction status: $transaction_status, Fraud status: $fraud_status");

    $new_status = 'pending';  // Default status

    if ($transaction_status == 'capture') {
        if ($fraud_status == 'challenge') {
            $new_status = 'challenge';
        } else if ($fraud_status == 'accept') {
            $new_status = 'success';
        }
    } else if ($transaction_status == 'settlement') {
        $new_status = 'success';
    } else if ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
        $new_status = 'failure';
    } else if ($transaction_status == 'pending') {
        $new_status = 'pending';
    }

    try {
        $stmt = $db->prepare("UPDATE transaksi SET status = ? WHERE order_id = ?");
        $stmt->execute([$new_status, $order_id]);

        error_log("Transaction status updated to: $new_status for order_id: $order_id");

        echo "Transaction status updated to: " . $new_status;
    } catch (Exception $e) {
        error_log("Error updating transaction status: " . $e->getMessage());
        http_response_code(500);
    }
}
