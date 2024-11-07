<?php
require 'function.php';
require 'cek.php';

$query = "SELECT * FROM products ORDER BY id ASC";
$result = mysqli_query($conn, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Produk</title>
    
    <!-- CSS -->
    <link href="css/style.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    
    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    /* Apply Poppins font globally */
    body {
        font-family: 'Poppins', sans-serif;
    }

    /* Enhanced navbar styling */
    .navbar-brand {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Navigation styling */
    .nav-link {
        font-size: 0.9rem;
        padding: 12px 20px;
        transition: all 0.3s ease;
    }

    .nav-link.active {
        background-color: #4a6cf7 !important;
        color: #fff !important;
        font-weight: 500;
        border-radius: 8px;
    }

    .nav-link:hover {
        background-color: rgba(74, 108, 247, 0.05);
        transform: translateX(5px);
    }

    .nav-link .sb-nav-link-icon {
        margin-right: 10px;
    }

    /* Sidebar menu container */
    .sb-sidenav-menu {
        padding: 1rem;
    }

    .sb-sidenav-menu .nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    /* Remove existing conflicting styles */
    .nav-link.active {
        background-color: #4a6cf7 !important;
        color: #fff !important;
    }

    .nav-link:hover {
        background-color: rgba(74, 108, 247, 0.05);
    }

    .nav-link.active .sb-nav-link-icon {
        color: #fff !important;
    }
</style>

<!-- Replace the existing sidebar navigation section in all three files with: -->
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                
                $menu_items = [
                    'produk' => ['file' => 'index.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Produk'],
                    'transaksi' => ['file' => 'transaksi.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Transaksi'],
                    'voucher' => ['file' => 'voucher.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Voucher'],
                    'settings' => ['file' => 'settings.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Settings'],
                    'logout' => ['file' => 'logout.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Logout']
                ];

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

        <!-- Main Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1 class="mt-4">Produk</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <a href="listproduct.php">
                                <button type="button" class="btn btn-dark">Lihat Halaman User</button>
                            </a>
                            
                            <!-- Product List -->
                            <?php foreach ($products as $product): ?>
                                <div class="row mb-3 mt-4">
                                    <div class="col-3"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="col-1">:</div>
                                    <div class="col-8">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" 
                                                class="custom-control-input product-visibility" 
                                                id="visibility_<?php echo $product['id']; ?>"
                                                data-product-id="<?php echo $product['id']; ?>" 
                                                <?php echo $product['visible'] ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="visibility_<?php echo $product['id']; ?>"></label>
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
                                    <div class="col-8">Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></div>
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

            <!-- Footer -->
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

    <!-- Product Visibility Toggle Script -->
    <script>
        $(document).ready(function() {
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
                                if (!result.success) {
                                    alert('Error updating visibility: ' + (result.error || 'Unknown error'));
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
                            checkbox.prop('checked', !isVisible);
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>