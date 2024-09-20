<?php
require 'function.php';
require 'cek.php';

// Set header untuk download file CSV
header('Content-Type: text');
header('Content-Disposition: attachment; filename="daftar_voucher.txt"');

// Buka output file untuk menulis
$output = fopen('php://output', 'w');

// Tulis header CSV
fputcsv($output, array('Kode', 'Jumlah Diskon', 'Status'));

// Ambil data voucher dari database
$ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");

// Tulis data voucher ke file CSV
while ($row = mysqli_fetch_assoc($ambilsemuadatavoucher)) {
    $status = ($row['is_used'] == 0) ? 'Belum Digunakan' : 'Sudah Digunakan';
    fputcsv($output, array($row['code'], $row['discount_amount'], $status));
}

// Tutup file
fclose($output);
exit;
?>