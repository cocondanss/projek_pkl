<?php
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'api.php';  // Pastikan file ini berisi fungsi midtrans_notification()

// Verifikasi signature dari Midtrans
$notification = new \Midtrans\Notification();

$order_id = $notification->order_id;
$status_code = $notification->status_code;
$gross_amount = $notification->gross_amount;
$signature_key = $notification->signature_key;

$server_key = 'SB-Mid-server-BiPEZ8YxMZheywHq49sAQthl';  // Gunakan server key yang sesuai
$hashed = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

if ($signature_key !== $hashed) {
    exit('Invalid signature');
}

// Jika signature valid, proses notifikasi
midtrans_notification();

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

        // Tambahkan kode berikut untuk mengarahkan pengguna ke halaman transberhasil.php jika transaksi berhasil
        if ($new_status == 'success') {
            // Simpan order_id pada session
            $_SESSION['order_id'] = $order_id;

            // Arahkan pengguna ke halaman transberhasil.php
            header('Location: transberhasil.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Error updating transaction status: " . $e->getMessage());
        http_response_code(500);
    }
}