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
            <p></p>
            <p>Terima kasih atas pembelian Anda.</p>
        </div>
        <a href="listproduct.php" class="btn btn-dark mr-2">Kembali ke Daftar Produk</a>
    </div>
</body>
</html>