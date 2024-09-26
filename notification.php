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