<?php
session_start();

// Periksa apakah ada data transaksi dalam session
if (!isset($_SESSION['successful_transaction'])) {
    // Jika tidak ada, arahkan kembali ke halaman produk
    header('Location: listproduct.php');
    exit();
}

$transactionData = $_SESSION['successful_transaction'];

// Mengonversi waktu dari UTC ke waktu lokal
$createdAtUTC = $transactionData['created_at'];
$tanggal = new DateTime($createdAtUTC, new DateTimeZone('UTC')); // Set zona waktu ke UTC
$tanggal->setTimezone(new DateTimeZone('Asia/Jakarta')); // Ubah ke zona waktu lokal
$formattedDate = $tanggal->format('d-m-Y H:i:s'); // Format tanggal sesuai kebutuhan

// Hapus data transaksi dari session setelah digunakan
unset($_SESSION['successful_transaction']);

// Ambil nama produk untuk judul halaman
$productName = htmlspecialchars($transactionData['product_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $productName; ?> Berhasil</title> <!-- Judul halaman sesuai produk -->
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
                <li>Produk: <?php echo $productName; ?></li> <!-- Menampilkan nama produk -->
                <li>Harga: Rp <?php echo number_format($transactionData['amount'], 0, ',', '.'); ?></li>
                <li>Tanggal: <?php echo $formattedDate; ?></li>
            </ul>
        </div>
        <a href="listproduct.php" class="btn btn-dark mr-2">Kembali ke Daftar Produk</a>
    </div>
</body>
</html>