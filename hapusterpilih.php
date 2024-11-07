<?php
require 'function.php';

$hapus_voucher = mysqli_query($conn, "DELETE FROM vouchers WHERE is_used = 1");

if ($hapus_voucher) {
    echo "Voucher yang sudah digunakan telah dihapus";
} else {
    echo "Gagal menghapus voucher";
}
?>