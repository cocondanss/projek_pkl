<?php
session_start();

// Periksa apakah ada data transaksi dalam session
if (!isset($_SESSION['successful_transaction'])) {
    // Jika tidak ada, arahkan kembali ke halaman produk
    header('Location: listproduct.php');
    exit();
}

$transactionData = $_SESSION['successful_transaction'];
// Format tanggal ke format Indonesia
$tanggal = date('d-m-Y H:i:s', strtotime($transactionData['created_at']));
// Hapus data transaksi dari session setelah digunakan
unset($_SESSION['successful_transaction']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Berhasil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Transaksi Berhasil!</h4>
            <p>Terima kasih atas pembelian Anda.</p>
            <hr>
            <p class="mb-0">Detail Transaksi:</p>
            <ul>
                <li>ID Transaksi: <?php echo htmlspecialchars($transactionData['transaction_id']); ?></li>
                <li>Produk: <?php echo htmlspecialchars($transactionData['product_name']); ?></li>
                <li>Harga: Rp <?php echo number_format($transactionData['amount'], 0, ',', '.'); ?></li>
                <li>Tanggal: <?php echo $tanggal; ?></li>
            </ul>
        </div>
        <a href="listproduct.php" class="btn btn-dark mr-2">Kembali ke Daftar Produk</a>
    </div>
</body>
</html>