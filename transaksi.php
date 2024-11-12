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
        <title>Transaksi</title>
        <link href="css/style.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
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

    /* Form styling */
    .form-label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    /* Navigation styling */
    .nav-link {
        font-size: 0.9rem;
        padding: 12px 20px;
        transition: all 0.3s ease;
    }

    .nav-link.active {
        background-color: #343A40 !important;
        color: #fff !important;
        font-weight: 500;
        border-radius: 8px;
    }

    .nav-link:hover {
        background-color: rgba(74, 108, 247, 0.05);
        transform: translateX(8px);
    }

    .nav-link .sb-nav-link-icon {
        margin-right: 10px;
    }

    /* Alert styling */
    .alert {
        border-radius: 8px;
        font-weight: 500;
    }

    /* Footer styling */
    footer {
        font-size: 0.85rem;
    }

    footer a {
        color: #4a6cf7;
        text-decoration: none;
    }

    footer a:hover {
        color: #2848dc;
    }
</style>
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
                        <h1 class="mt-4">Transaksi</h1>
                        </ol>
                        <div class="card mb-4">
                            <form method="post">
                            <div class="card-header">
                                <button type="submit" name="hapustransaksi" class="btn btn-dark mr-2">
                                    Hapus Transaksi Terpilih
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>ID Transaksi</th>
                                                <th>Nama Barang</th>
                                                <th>Harga</th>
                                                <th>Tanggal Terima</th>
                                                <th>Status</th>
                                                <th> Pilih Semua <p></p><input type="checkbox" id="selectAll"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $ambilsemuadatatransaksi = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY created_at DESC");
                                            $i = 1;
                                            while($data=mysqli_fetch_array($ambilsemuadatatransaksi)){
                                                $order_id = $data['order_id'];
                                                $product_name = $data['product_name'];
                                                $price = $data['price'];
                                                $tanggal = date('d-m-Y H:i:s', strtotime($data['created_at']));
                                                $status = $data['status'];
                                        ?>
                                            <tr>
                                                <td><?=$i++;?></td>
                                                <td><?=$order_id;?></td>
                                                <td><?=$product_name;?></td>
                                                <td>Rp<?=number_format($price, 0, ',', '.');?></td>
                                                <td><?=$tanggal;?></td>
                                                <td><?=$status;?></td>
                                                <td><input type="checkbox" name="delete[]" value="<?=$order_id;?>"></td>
                                            </tr>
                                        <?php
                                            };
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
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
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
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
            document.getElementById('selectAll').onclick = function() {
                var checkboxes = document.getElementsByName('delete[]');
                for(var checkbox of checkboxes) {
                    checkbox.checked = this.checked;
                }
            }
        </script>
    </body>
</html>
