<?php
require 'function.php';
require 'cek.php';

$query = "SELECT * FROM products  ORDER BY id ASC";
$result = mysqli_query($conn, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
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
        <link href="css/styleP.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    </head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php" style="color: white;">Daclen</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
    </nav>
    <div id="layoutSidenav">
        <!-- Sidebar Navigasi -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <?php
                        // Mendapatkan nama file halaman saat ini
                        $current_page = basename($_SERVER['PHP_SELF']);
                        
                        // Array menu navigasi
                        $menu_items = [
                            'produk' => ['file' => 'index.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Produk'],
                            'transaksi' => ['file' => 'transaksi.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Transaksi'],
                            'voucher' => ['file' => 'voucher.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Voucher'],
                            'settings' => ['file' => 'settings.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Settings'],
                            'logout' => ['file' => 'logout.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Logout']
                        ];

                        // Membuat menu items
                        foreach ($menu_items as $key => $item) {
                            $isActive = ($current_page === $item['file']) || 
                                      ($current_page === 'index.php' && $key === 'produk');
                            $activeClass = $isActive ? 'active' : '';
                            
                            echo '<a class="nav-link ' . $activeClass . '" href="' . $item['file'] . '">
                                    <div class="sb-nav-link-icon"><i class="' . $item['icon'] . '"></i></div>
                                    ' . $item['text'] . '
                                </a>';
                        }
                        ?>
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
                                    <div class="row mb-3">
                                        <div class="col-3"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div class="col-1">:</div>
                                        <div class="col-8">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                    class="custom-control-input product-visibility" 
                                                    id="visibility_<?php echo $product['id']; ?>"
                                                    data-product-id="<?php echo $product['id']; ?>" 
                                                    <?php echo $product['visible'] ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="visibility_<?php echo $product['id']; ?>">
                                                    <!-- <?php echo $product['visible'] ? 'Visible' : 'Hidden'; ?> -->
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">Id</div>
                                        <div class="col-1">:</div>
                                        <div class="col-8"><?php echo $product['id']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">Harga</div>
                                        <div class="col-1">:</div>
                                        <div class="col-8">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>,00</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">Deskripsi</div>
                                        <div class="col-1">:</div>
                                        <div class="col-8"><?php echo $product['description']; ?></div>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <!-- <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script> -->
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/datatables-demo.js"></script>
        <script>
            // Fungsi untuk select/deselect semua checkbox
            $(document).ready(function() {
                    // Initialize checkbox states
                    $('.product-visibility').each(function() {
                        var checkbox = $(this);
                        var productId = checkbox.data('product-id');
                        
                        checkbox.change(function() {
                            var isVisible = $(this).prop('checked') ? 1 : 0;
                            
                            $.ajax({
                                url: 'update_product_visibility.php',
                                method: 'POST',
                                data: { 
                                    product_id: productId,
                                    visible: isVisible 
                                },
                                success: function(response) {
                                    try {
                                        var result = JSON.parse(response);
                                        if (result.success) {
                                            // Tampilkan notifikasi sukses
                                            // alert('Status visibility berhasil diupdate');
                                        } else {
                                            alert('Error updating visibility: ' + (result.error || 'Unknown error'));
                                            // Kembalikan status checkbox jika terjadi error
                                            checkbox.prop('checked', !isVisible);
                                        }
                                    } catch (e) {
                                        console.error('Error parsing response:', e);
                                        alert('Error updating visibility');
                                        checkbox.prop('checked', !isVisible);
                                    }
                                },
                                error: function() {
                                    alert('Error connecting to server');
                                    // Kembalikan status checkbox jika terjadi error
                                    checkbox.prop('checked', !isVisible);
                                }
                            });
                        });
                    });
                });
        </script>
    </body>
</html>
