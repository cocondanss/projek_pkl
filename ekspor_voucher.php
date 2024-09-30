<?php
require 'function.php';
require 'cek.php';

// Set header untuk file download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="daftar_voucher.txt"');

// Ambil data voucher dari database
$ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");

// Tulis data voucher
echo "Kode Voucher | Jumlah Diskon | Status | Tanggal Digunakan\n";
echo "----------------------------------------------------\n";
while ($row = mysqli_fetch_assoc($ambilsemuadatavoucher)) {
    $status = ($row['is_used'] == 0) ? 'Belum Digunakan' : 'Sudah Digunakan';
    $used_at = $row['used_at'] ? $row['used_at'] : '-';
    echo $row['code'] . ' | ' . $row['discount_amount'] . ' | ' . $status . ' | ' . $used_at . "\n";
}

exit;