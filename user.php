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
        <title>User</title>
        <link href="css/style.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand" href="index.php" style="color: white;">Daclen</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
            <!-- Modifikasi pada bagian nav di index.php dan halaman lainnya -->
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                        <div class="sb-sidenav-menu">
                            <div class="nav">
                                <?php
                                // Get current page filename
                                $current_page = basename($_SERVER['PHP_SELF']);
                                
                                // Array of menu items with their corresponding files and icons
                                $menu_items = [
                                    'user' => ['file' => 'user.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'User'],
                                    'produk' => ['file' => 'index.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Produk'],
                                    'transaksi' => ['file' => 'transaksi.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Transaksi'],
                                    'voucher' => ['file' => 'voucher.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Voucher'],
                                    'settings' => ['file' => 'settings.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Settings'],
                                    'logout' => ['file' => 'logout.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Logout']
                                ];

                                // Generate menu items
                                foreach ($menu_items as $key => $item) {
                                    // Check if current page is index.php and menu item is produk
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

                    <style>
                    /* Add this to your style.css file */
                    .nav-link.active {
                        background-color: rgba(255, 255, 255, 0.1);
                        color: #fff !important;
                        font-weight: 500;
                    }

                    .nav-link {
                        transition: background-color 0.2s ease-in-out;
                    }

                    .nav-link:hover {
                        background-color: rgba(255, 255, 255, 0.05);
                    }

                    /* Tambahan untuk memastikan ikon juga terlihat lebih jelas saat aktif */
                    .nav-link.active .sb-nav-link-icon {
                        color: #fff;
                    }
                    </style>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <h1 class="mt-4">User</h1>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal">
                                    Tambah User
                             </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama User</th>
                                                <th>Status</th>
                                                <th>Umur</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                            $ambilsemuadatauser = mysqli_query($conn, "select * from user");
                                            $i = 1;
                                            while($data=mysqli_fetch_array($ambilsemuadatauser)){
                                                $namauser = $data['namauser'];
                                                $status = $data['status'];
                                                $umur = $data['umur'];
                                                $idu = $data['iduser'];


                                            ?>

                                            <tr>
                                                <td><?=$i++;?></td>
                                                <td><?=$namauser;?></td>
                                                <td><?=$status;?></td>
                                                <td><?=$umur;?></td>
                                                <td>
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?=$idu;?>">
                                                        Edit
                                                </button>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?=$idu;?>">
                                                        Delete
                                                </button>
                                                </td>
                                            </tr>


                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="edit<?=$idu;?>">
                                                <div class="modal-dialog">
                                                <div class="modal-content">
                                                
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                    <h4 class="modal-title">Edit User</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    
                                                    <!-- Modal body -->
                                                    <form method="post">
                                                    <div class="modal-body">
                                                    <input type="text" name="namauser" value="<?=$namauser;?>" class="form-control" required><br>
                                                    <input type="text" name="status" value="<?=$status;?>" class="form-control" required><br>
                                                    <input type="number" name="umur" value="<?=$umur;?>" class="form-control" required><br>
                                                    <input type="hidden" name="idu" value="<?=$idu?>">
                                                    <button type="submit" class="btn btn-primary" name="updateuser">Submit</button><br>
                                                    </div>
                                                    </form>

                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Delete Modal -->
                                            <div class="modal fade" id="delete<?=$idu;?>">
                                                <div class="modal-dialog">
                                                <div class="modal-content">
                                                
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                    <h4 class="modal-title">Hapus User?</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    
                                                    <!-- Modal body -->
                                                    <form method="post">
                                                    <div class="modal-body">
                                                    Apakah Anda Yakin Ingin Menghapus <?=$namauser;?>?
                                                    <input type="hidden" name="idu" value="<?=$idu?>">
                                                    <br>
                                                    <br>
                                                    <button type="submit" class="btn btn-danger" name="hapususer">Hapus</button><br>
                                                    </div>
                                                    </form>

                                                    </div>
                                                    </div>
                                                </div>


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
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/datatables-demo.js"></script>
    </body>

    <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Tambah User</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
         <form method="post">
         <div class="modal-body">
        <input type="text" name="namauser" placeholder="Nama User" class="form-control" required><br>
        <input type="text" name="status" placeholder="Status User" class="form-control" required><br>
        <input type="number" name="umur" placeholder="Umur User" class="form-control" required><br>
        <button type="submit" class="btn btn-primary" name="TambahUser">Submit</button><br>
        </div>
        </form>

    </div>
    </div>
</div>
</html>
