<?php
require 'function.php';
require 'cek.php';
?>

<html lang="en">
    <head>
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
            <meta name="description" content="" />
            <meta name="author" content="" />
            <title>Produk</title>
            <link href="css/style.css" rel="stylesheet" />
            <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
            <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            </head>
        <body class="sb-nav-fixed">
            <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
                <a class="navbar-brand" href="index.php" style="color: white;">Daclen</a>
                <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
            </nav>
            <div id="layoutSidenav">
                <div id="layoutSidenav_nav">
                    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                        <div class="sb-sidenav-menu">
                            <div class="nav">
                            <a class="nav-link" href="user.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    User
                                </a>
                                <a class="nav-link active" href="index.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Produk
                                </a>
                                <a class="nav-link" href="transaksi.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Transaksi
                                </a>
                                <a class="nav-link" href="voucher.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Voucher
                                </a>
                                <a class="nav-link" href="logout.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>
                <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <h1 class="mt-4">Produk</h1>
                        <div class="card mb-4">
                            <div class="card-header">
                                <a href="listproduct.php">
                                    <button type="button" class="btn btn-dark">
                                        Lihat Halaman User
                                    </button>
                                </a>
                                <p></p>
                                <p></p>
                                <?php foreach ($products as $product): ?>
                                    <div class="row">
                                        <div class="col-3"><?php echo $product['name']; ?></div>
                                        <div class="col-1">:</div>
                                        <div class="col-8">
                                            <input type="checkbox" class="product-visibility" 
                                                data-product-id="<?php echo $product['id']; ?>" 
                                                <?php echo $product['visible'] ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">Deskripsi</div>
                                        <div class="col-1">:</div>
                                        <div class="col-8"><?php echo $product['description']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">Harga</div>
                                        <div class="col-1">:</div>
                                        <div class="col-8">Rp<?php echo number_format($product['price'], 2); ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">Id</div>
                                        <div class="col-1">:</div>
                                        <div class="col-8"><?php echo $product['id']; ?></div>
                                    </div>
                                    <hr>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </main>
                    <footer class="py-4 bg-light mt-auto">
                        <div class="container-fluid">
                            <div class="d-flex align-items-center justify-content-between small">
                                <div class="text-muted">Copyright &copy; Your Website 2020</div>
                                <div>
                                    <a href="#">Privacy Policy</a>
                                    &middot;
                                    <a href="#">Terms &amp; Conditions</a>
                                </div>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $('.product-visibility').change(function() {
                        var productId = $(this).data('product-id');
                        var isVisible = $(this).is(':checked');
                        
                        $.ajax({
                            url: 'update_product_visibility.php',
                            method: 'POST',
                            data: { 
                                product_id: productId, 
                                visible: isVisible ? 1 : 0 
                            },
                            success: function(response) {
                                console.log('Visibility updated');
                            },
                            error: function() {
                                console.log('Error updating visibility');
                            }
                        });
                    });
                });
            </script>
        </body>
    </head>
</html>