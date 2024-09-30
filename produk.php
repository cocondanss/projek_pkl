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
                            <a class="nav-link" href="index.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    User
                                </a>
                                <a class="nav-link" href="produk.php">
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
                            </ol>
                            <div class="card mb-4">
                                <div class="card-header">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal">
                                        Tambah Produk
                                </button>
                                <a href="listproduct.php">
                                <button type="button" class="btn btn-success">
                                        Lihat List Produk
                                </button>
                                </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Produk</th>
                                                    <th>Harga</th>
                                                    <th>Diskon</th>
                                                    <th>Stok</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <div>
                                            <?php
                                                $ambilsemuadataproduk = mysqli_query($conn, "select * from products");
                                                
                                                $i = 1;
                                                while($data=mysqli_fetch_array($ambilsemuadataproduk)){
                                                    $id = $data['id'];
                                                    $name = $data['name'];
                                                    $price = $data['price'];
                                                    $discount = $data['discount'];
                                                    $stok = $data['stok'];
                                                    
                                                    $diskon = ($discount == 1) ? "Ada" : "Tidak Ada";
                                                ?>
                                                <tr>
                                                    <td><?=$i++;?></td>
                                                    <td><?=$name;?></td>
                                                    <td><?=$price;?></td>
                                                    <td><?=$diskon;?></td>
                                                    <td><?=$stok;?></td>
                                                    <td>
                                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?=$id;?>">
                                                            Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?=$id;?>">
                                                            Delete
                                                    </button>
                                                    </td>
                                                </tr>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="edit<?=$id; ?>">
                                                    <div class="modal-dialog">
                                                    <div class="modal-content">
                                                    
                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                        <h4 class="modal-title">Edit Produk</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        
                                                        <!-- Modal body -->
                                                        <form method="post">
                                                        <div class="modal-body">
                                                        <input type="text" name="name" value="<?=$name;?>" class="form-control" required><br>
                                                        <input type="number" name="price" value="<?=$price;?>" class="form-control" required><br>
                                                        <select name="discount" class="form-control" required>
                                                            <option value="1" <?=$discount == '1' ? 'selected' : '';?>>Ada</option>
                                                            <option value="0" <?=$discount == '0' ? 'selected' : '';?>>Tidak Ada</option>
                                                        </select><br>
                                                            <input type="number" name="stok" value="<?=$stok;?>" class="form-control" required><br>
                                                        <input type="hidden" name="id" value="<?=$id;?>">
                                                        <button type="submit" class="btn btn-primary" name="updatebarang">Submit</button><br>
                                                        </div>
                                                        </form>

                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                    <!-- Delete Modal -->
                                                <div class="modal fade" id="delete<?=$id; ?>">
                                                    <div class="modal-dialog">
                                                    <div class="modal-content">
                                                    
                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                        <h4 class="modal-title">Hapus Produk?</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        
                                                        <!-- Modal body -->
                                                        <form method="post">
                                                        <div class="modal-body">
                                                        Apakah Anda Yakin Ingin Menghapus <?=$name;?>?
                                                        <input type="hidden" name="id" value="<?=$id?>">
                                                        <br>
                                                        <br>
                                                        <button type="submit" class="btn btn-danger" name="hapusbarang">Hapus</button><br>
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
            <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
            <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
            <script src="assets/demo/datatables-demo.js"></script>
        </body>
        <!-- The Modal -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
        <div class="modal-content">
        
            <!-- Modal Header -->
            <div class="modal-header">
            <h4 class="modal-title">Tambah Produk</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <!-- Modal body -->
            <form method="post">
            <div class="modal-body">
            <input type="text" name="name" placeholder="Nama Barang/Produk" class="form-control" required><br>
            <input type="number" name="price" placeholder="Harga Barang" class="form-control" required><br>
            <select name="discount" class="form-control" required>
                <option value="0" <?=$discount == '1' ? 'selected' : '';?>>Ada</option>
                <option value="0" <?=$discount == '0' ? 'selected' : '';?>>Tidak Ada</option>
            </select><br>
            <input type="number" name="stok" placeholder="Stok Barang" class="form-control" required><br>
            <button type="submit" class="btn btn-primary" name="TambahProduk">Submit</button><br>
            </div>
            </form>
    </html>
