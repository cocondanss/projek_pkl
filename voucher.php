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
            <title>Voucher</title>
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
                                <a class="nav-link" href="user.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    User
                                </a>
                                <a class="nav-link" href="index.php">
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
                            <form method="post" action="voucher.php">
                            <h1 class="mt-4">Voucher</h1>
                            <div class="card mb-4">
                            <div class="card-header">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal">
                                    Tambah Voucher
                                </button>
                                <button type="button" class="btn btn-dark" id="eksporVoucher">Ekspor Voucher Keseluruhan
                                </button>
                                <button type="submit" name="hapusvoucher" class="btn btn-dark">
                                    Hapus Voucher Terpilih
                                </button>
                                <button type="submit" class="btn btn-dark" name="hapus_voucher_digunakan">
                                    Hapus Voucher yang Sudah Digunakan
                                </button>
                            </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Code</th>
                                                    <th>Diskon</th>
                                                    <th>Status</th>
                                                    <th>Tanggal Dibuat</th>
                                                    <th>Tanggal Digunakan</th>
                                                    <th>Aksi</th>
                                                    <input type="checkbox" id="selectAll" onclick="toggle(this)"> Pilih Semua Voucher<br><br>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");
                                                    $i = 1;
                                                    while($data = mysqli_fetch_array($ambilsemuadatavoucher)){
                                                        $code = $data['code'];
                                                        $is_used = $data['is_used']; // <-- Penambahan definisi variabel $is_used
                                                        $id = $data['id'];
                                                        $created_at = $data['created_at'];
                                                        $used_at = $data['used_at'];
                                                        $status = ($is_used == 1) ? "Sudah digunakan" : "Belum digunakan";
                                                ?>
                                                    <tr>
                                                        <td><?=$i++;?></td>
                                                        <td><?=$code;?></td>
                                                        <td><?=$status;?></td>
                                                        <td><?=$created_at;?></td>
                                                        <td><?=$used_at ? $used_at : '-';?></td>
                                                        <td><input type="checkbox" name="delete[]" value="<?=$id;?>"></td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </form>
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
            <!-- The Modal -->
            <div class="modal fade" id="myModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Voucher</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="post" action="voucher.php">
                        <div class="modal-body">
                        <form method="post">
                        <input type="text" name="code_prefix" placeholder="Kode Voucher" class="form-control" required><br>
                        <input type="number" name="discount_amount" placeholder="Jumlah Diskon" class="form-control" required><br>
                        <input type="number" name="voucher_count" placeholder="Jumlah Voucher" class="form-control" min="1" required><br>
                        <button type="submit" name="simpan_ekspor" class="btn btn-primary">Tambah & Ekspor</button>
                        </div>
                    </form>
                </div>
                </div>
            </div>           
    </form>
    <script>
    $(document).ready(function() {
       $ ("#eksporVoucher").click(function() {
        var table = $('#dataTable').DataTable();
        var data = table.data().toArray();
        
        var fileContent = 'Kode Voucher | Jumlah Diskon | Status | Tanggal Digunakan\n';
        data.forEach(function(row) {
            fileContent += row[1] + ' | ' + row[2] + ' | ' + row[3] + ' | ' + row[5] + '\n';
        });

        var blob = new Blob([fileContent], {type: 'text/plain'});
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'daftar_voucher.txt';
        link.click();

            $.ajax({
                type: 'POST',
                url: 'ekspor_voucher.php',
                data: {},
                success: function(data) {
                    // kode yang sudah ada
                },
                error: function(xhr, status, error) {
                    console.error("Terjadi kesalahan: " + error);
                    alert("Gagal mengekspor data. Silakan coba lagi.");
                }
            });
        });

        // Aktifkan fungsi checkbox pilih semua voucher
        $('#selectAll').click(function() {
            $('input[type="checkbox"]').prop('checked', this.checked);
        });
    });
        </script>
    </body>
</html>