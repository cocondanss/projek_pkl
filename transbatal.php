<?php
session_start();

if (!isset($_SESSION['cancelled_transaction'])) {
    header('Location: listproduct.php');
    exit;
}

$transactionData = $_SESSION['cancelled_transaction'];
$tanggal = date('d-m-Y', strtotime($transactionData['created_at']));
unset($_SESSION['cancelled_transaction']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Dibatalkan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Transaksi Dibatalkan!</h4>
            <p>Transaksi Anda telah dibatalkan.</p>
            <hr>
            <p class="mb-0">Detail Transaksi:</p>
            <ul>
                <li>ID Transaksi: <?php echo htmlspecialchars($transactionData['transaction_id']); ?></li>
                <li>Produk: <?php echo htmlspecialchars($transactionData['product_name']); ?></li>
                <li>Harga: Rp <?php echo number_format($transactionData['amount'], 0, ',', '.'); ?></li>
                <li>Tanggal: <?php echo $tanggal; ?></li>
            </ul>
        </div>
        <a href="listproduct.php" class="btn btn-primary">Kembali ke Daftar Produk</a>
    </div>
</body>
</html>