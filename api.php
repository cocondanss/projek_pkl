<?php
session_start();
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
                    if ($data['action'] === 'create_transaction') {
                        create_transaction($data);
                    } elseif($data['action'] === 'check_payment_status') {
                        check_payment_status($data);
                    } elseif($data['action'] === 'cancel_transaction') {
                        cancel_transaction($data);
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

function create_transaction($data) {
    global $db;

    if (!isset($data['product_id']) || !isset($data['product_name']) || !isset($data['product_price'])) {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    try {
        $order_id = 'TRX-' . time() . '-' . uniqid();
        $product_id = $data['product_id'];
        $product_name = $data['product_name'];
        $product_price = intval($data['product_price']);
        $discount = isset($data['discount']) ? intval($data['discount']) : 0;
        $total_price = $product_price - $discount;
        $status = 'pending';

        $stmt = $db->prepare("INSERT INTO transaksi (order_id, product_id, product_name, price, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $product_id, $product_name, $total_price, $status]);

        if ($total_price < 0) {
            $total_price = 0;
        }

        $transaction_details = array(
            'order_id' => $order_id,
            'gross_amount' => $total_price
        );

        $item_details = array(
            array(
                'id' => $product_id,
                'price' => $total_price,
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

        $params = array(
            'payment_type' => 'qris',
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details
        );

        $qris_response = \Midtrans\CoreApi::charge($params);

        if (isset($qris_response->actions)) {
            foreach ($qris_response->actions as $action) {
                if ($action->name == 'generate-qr-code') {
                    $qr_code_url = $action->url;
                    break;
                }
            }
        }

        if (!isset($qr_code_url)) {
            throw new Exception("QR code URL not found in the response");
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

function check_payment_status($data) {
    global $db;

    if (!isset($data['transaction_id'])) {
        echo json_encode(["success" => false, "message" => "Missing transaction ID"]);
        return;
    }

    try {
        $status = \Midtrans\Transaction::status($data['transaction_id']);
        
        if (is_object($status)) {
            $transaction_status = $status->transaction_status;
        } elseif (is_array($status)) {
            $transaction_status = $status['transaction_status'] ?? null;
        } else {
            throw new Exception("Unexpected response type from Midtrans");
        }

        if ($transaction_status === null) {
            throw new Exception("Transaction status not found in Midtrans response");
        }

        $stmt = $db->prepare("UPDATE transaksi SET status = ? WHERE order_id = ?");
        $stmt->execute([$transaction_status, $data['transaction_id']]);

        // Tambahkan data transaksi ke session jika status settlement
        if ($transaction_status === 'settlement') {
            $stmt = $db->prepare("SELECT * FROM transaksi WHERE order_id = ?");
            $stmt->execute([$data['transaction_id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($transaction) {
                $_SESSION['successful_transaction'] = [
                    'transaction_id' => $transaction['order_id'],
                    'product_name' => $transaction['product_name'],
                    'amount' => $transaction['price'],
                    'created_at' => $transaction['created_at'] // Tambahkan created_at
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

    function cancel_transaction($data) {
        global $db;

        if (!isset($data['transaction_id'])) {
            echo json_encode(["success" => false, "message" => "Missing transaction ID"]);
            return;
        }

        try {
            // Update status transaksi menjadi 'cancelled'
            $stmt = $db->prepare("UPDATE transaksi SET status = 'cancelled' WHERE order_id = ?");
            $stmt->execute([$data['transaction_id']]);

            // Ambil data transaksi untuk disimpan di session
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
                "message" => "Transaction cancelled successfully"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

// Endpoint untuk menerima notifikasi dari Midtrans
function midtrans_notification() {
    global $db;

    // Ambil notifikasi dari Midtrans
    $notif = new \Midtrans\Notification();

    $order_id = $notif->order_id;
    $transaction_status = $notif->transaction_status;

    try {
        // Perbarui status transaksi di database berdasarkan notifikasi Midtrans
        $stmt = $db->prepare("UPDATE transaksi SET status = ? WHERE order_id = ?");
        $stmt->execute([$transaction_status, $order_id]);

        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
