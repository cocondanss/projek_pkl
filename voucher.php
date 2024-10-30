<?php
require_once 'function.php';
require 'cek.php';

if (isset($_POST['hapusVoucherYangSudahDigunakan'])) {
    // Query untuk menghapus voucher yang sudah digunakan dan sekali pakai
    $sql = "DELETE FROM vouchers2 WHERE used_at IS NOT NULL AND one_time_use = 1"; // Hanya hapus voucher yang sudah digunakan dan sekali pakai

    if ($conn->query($sql) === TRUE) {
        echo "Voucher yang sudah digunakan dan sekali pakai berhasil dihapus.";
    } else {
        echo "Error: " . $conn->error; // Tampilkan kesalahan jika ada
        // Tambahkan log untuk debugging
        error_log("Query failed: " . $sql);
    }
}

function generateVoucherCode($length = 8) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$voucherCode = generateVoucherCode(); // Buat kode voucher otomatis setiap kali halaman dibuka

if (isset($_POST['TambahVoucherOtomatis'])) {
    $voucherCode = $_POST['code_prefix'];
    $voucherCount = $_POST['voucher_count'];
    $voucherType = $_POST['voucherType'];
    $nominalVoucher = $_POST['nominalVoucher'];
    $diskonVoucher = $_POST['diskonVoucher'];

    if ($voucherType == 'rupiah') {
        $discountAmount = $nominalVoucher;
    } elseif ($voucherType == 'diskon') {
        $discountAmount = $diskonVoucher;
    }

    $query = "ALTER TABLE vouchers2 CHANGE id id INT AUTO_INCREMENT;";
mysqli_query($conn, $query);

for ($i = 0; $i < $voucherCount; $i++) {
    $voucherCode = generateVoucherCode();
    $query = "INSERT INTO vouchers2 (code, discount_amount) VALUES ('$voucherCode', '$discountAmount')";
    mysqli_query($conn, $query);
}

    unset($voucherCode);
    unset($discountAmount);
    $voucherCode = $_POST['code_prefix'];
    // echo "<script>alert('Voucher berhasil ditambahkan');</script>";
    header('Location: voucher.php');
}
if (isset($_POST['TambahVoucherManual'])) {
    $manualCode = trim($_POST['manual_code']);
    $manualCode = mysqli_real_escape_string($conn, $manualCode);
    $nominal = $_POST['nominal'];
    $isFree = isset($_POST['is_free']) ? 1 : 0;
    $oneTimeUse = isset($_POST['one_time_use']) ? 1 : 0;

    $query = "INSERT INTO vouchers2 (code, discount_amount, is_free, one_time_use) 
              VALUES ('$manualCode', '$nominal', '$isFree', '$oneTimeUse') 
              ON DUPLICATE KEY UPDATE 
              discount_amount = VALUES(discount_amount), 
              is_free = VALUES(is_free), 
              one_time_use = VALUES(one_time_use)";
    
    if (mysqli_query($conn, $query)) {
        header('Location: voucher.php?status=success&message=Voucher manual berhasil ditambahkan');
        exit();
    } else {
        header('Location: voucher.php?status=error&message=Gagal menambahkan voucher');
        exit();
    }
}

    // echo "<script>alert('Voucher manual berhasil ditambahkan');</script>";

 if (isset($_POST['hapusvoucher'])) {
    $id = $_POST['delete'];
    $query = "DELETE FROM vouchers2 WHERE id IN (" . implode(',', $id) . ")";
    mysqli_query($conn, $query);
    header('Location: voucher.php');
}
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
    <style>
        .hidden { display: none; }
    </style>                    
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
                        <a class="nav-link" href="settings.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Settings
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
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#voucherModal">
                                    Tambah Voucher otomatis
                                </button>
                                <button type="button" class="btn btn-info" id="eksporVoucher">
                                    Ekspor Voucher
                                </button>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#manualVoucherModal">
                                    Tambah Voucher Manual
                                </button>
                                <button type="submit" name="hapusvoucher" id="hapusvoucher" class="btn btn-danger">
                                    Hapus Voucher Terpilih
                                </button>
                                <p></p>
                                <form method="POST" action="voucher.php">
                                    <button type="submit" name="hapusVoucherYangSudahDigunakan" class="btn btn-danger" id="btnHapusVoucher" onclick="return confirm('Apakah Anda yakin ingin menghapus semua voucher yang sudah digunakan?');">
                                        Hapus Voucher yang Sudah Digunakan
                                    </button>
                                </form>
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
                                                <th>gratis</th>
                                                <th>sekali pakai</th>
                                                <th>Tanggal Dibuat</th>
                                                <th>Tanggal Digunakan</th>
                                                <th>Aksi</th>
                                                <input type="checkbox" id="selectAll" onclick="toggle(this)"> Pilih Semua Voucher<br><br>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers2");
                                                $i = 1;
                                                while ($data = mysqli_fetch_array($ambilsemuadatavoucher)) {
                                                    $code = $data['code'];
                                                    $discount_amount = $data['discount_amount'];
                                                    $is_free = $data['is_free'];
                                                    $one_time_use = $data['one_time_use'];
                                                    $id = $data['id'];
                                                    $created_at = $data['created_at'];
                                                    $used_at = $data['used_at'];

                                                    // Tentukan status berdasarkan used_at
                                                    $status_used = !empty($used_at) ? "Sudah digunakan" : "Belum digunakan";

                                                    $isFreeDisplay = ($is_free == 1) ? "Ya" : "Tidak";
                                                    $oneTimeUse = ($one_time_use == 1) ? "Ya" : "Tidak";

                                                    // Jika voucher gratis, set discount_amount menjadi 0
                                                    if ($is_free == 1) {
                                                        $discount_amount = 0;
                                                    }

                                                    // Tentukan jenis voucher (diskon atau rupiah)
                                                    $voucherType = ($discount_amount > 100) ? 'rupiah' : 'diskon';
                                                ?>
                                                    <tr>
                                                        <td><?= $i++; ?></td>
                                                        <td><?= htmlspecialchars($code); ?></td>
                                                        <td>
                                                            <?php if ($is_free == 1): ?>
                                                                0
                                                            <?php elseif ($voucherType == 'diskon'): ?>
                                                                <?= htmlspecialchars($discount_amount) . '%' ?>
                                                            <?php else: ?>
                                                                <?= 'Rp ' . number_format($discount_amount, 0, ',', '.') ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($status_used); ?></td> <!-- Menampilkan status -->
                                                        <td><?= htmlspecialchars($isFreeDisplay); ?></td>
                                                        <td><?= htmlspecialchars($oneTimeUse); ?></td>
                                                        <td><?= htmlspecialchars($created_at); ?></td>
                                                        <td><?= !empty($used_at) ? htmlspecialchars($used_at) : '-'; ?></td>
                                                        <td><input type="checkbox" name="delete[]" value="<?= htmlspecialchars($id); ?>"></td>
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
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/datatables-demo.js"></script>
    </div>

    <!-- input Tambah Voucher otomatis -->


    <div class="modal fade" id="voucherModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Voucher otomatis</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post">
                    <div class="modal-body">
                    <label for="code_prefix">Kode Voucher:</label>
                        <!-- Kode Voucher diisi otomatis -->
                        <input type="text" name="code_prefix" class="form-control" value="<?= $voucherCode; ?>" readonly><br>
                        
                        <input type="number" name="voucher_count" placeholder="Jumlah Voucher" class="form-control" min="1" required> <br>

                    <label>Jenis Voucher:</label><br>
                    <label><input type="radio" name="voucherType" value="rupiah" id="rupiahRadio" required> Rupiah</label>
                    <label><input type="radio" name="voucherType" value="diskon" id="diskonRadio" required> Diskon</label><br><br>

                    <div id="nominalInput" class="hidden">
                        <label for="nominalVoucher">Nominal Voucher (Rupiah):</label>
                        <input type="number" name="nominalVoucher" id="nominalVoucher" class="form-control" step="1000"><br>
                    </div>
                    <div id="diskonInput" class="hidden">
                        <label for="diskonVoucher">Diskon (%):</label>
                        <input type="number" name="diskonVoucher" id="diskonVoucher" class="form-control" min="1" max="100">
                        <span>%</span><br><br>
                    </div>
                    <button type="submit" class="btn btn-primary" name="TambahVoucherOtomatis">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>   

    <!-- Input Tambah Vocher Manual -->
    <div class="modal fade" id="manualVoucherModal">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Tambah Voucher Manual</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="post" id="voucherForm">
                                    <div class="modal-body">
                                        <!-- Input Voucher Code -->
                                        <input type="text" name="manual_code" placeholder="Kode Voucher" class="form-control" required><br>

                                        <!-- Nominal (Rp) Input -->
                                        <input type="number" name="nominal" placeholder="Nominal (Rp)" class="form-control" required><br>

                                        <!-- Checkbox for Free Option -->
                                        <input type="checkbox" name="is_free" id="isFree" onchange="toggleNominal()"> 
                                        <label for="isFree">Gratis</label><br><br>

                                        <!-- Checkbox for One-Time Use -->
                                        <input type="checkbox" name="one_time_use" id="oneTimeUse"> 
                                        <label for="oneTimeUse">Sekali Pakai</label><br><br>

                                        <!-- Button to Create Voucher -->
                                        <button type="submit" class="btn btn-primary" name="TambahVoucherManual">Tambah Voucher Manual</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>






            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
            <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
            <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

         <script>
            // Fungsi untuk menampilkan input berdasarkan pilihan radio
                                    function showInput() {
                                    const rupiahRadio = document.getElementById('rupiahRadio');
                                    const diskonRadio = document.getElementById('diskonRadio');
                                    const nominalInput = document.getElementById('nominalInput');
                                    const diskonInput = document.getElementById('diskonInput');

                                    if (rupiahRadio.checked) {
                                    nominalInput.classList.remove('hidden');
                                    diskonInput.classList.add('hidden');
                                    } else if (diskonRadio.checked) {
                                    diskonInput.classList.remove('hidden');
                                    nominalInput.classList.add('hidden');
                                    }
                                }

                                // Jalankan fungsi showInput ketika halaman dimuat pertama kali
                                showInput();

                                // Tambahkan event listener ke radio button
                                document.getElementById('rupiahRadio').addEventListener('change', showInput);
                                document.getElementById('diskonRadio').addEventListener('change', showInput);

                                // Script untuk ekspor data voucher
                                $("#eksporVoucher").click(function(event) {
                                event.preventDefault();
                                var table = $('#dataTable').DataTable(); // Ubah id tabel menjadi dataTable
                                var data = table.rows().data();

                                var fileContent = 'Kode Voucher\n';
                                data.each(function(value, index) {
                                var code = value
                                var code = value[1];

                                fileContent += code + '\n';
                                });

                                var blob = new Blob([fileContent], {type: 'text/plain'});
                                var link = document.createElement('a');
                                link.href = URL.createObjectURL(blob);
                                link.download = 'daftar_voucher.txt';
                                link.click();

                                $.ajax({
                                type: 'POST',
                                url: 'ekspor_voucher.php',
                                success: function(data) {
                                    console.log("Data berhasil diunduh");
                                },
                                error: function(xhr, status, error) {
                                    console.error("Terjadi kesalahan: " + error);
                                    alert("Gagal mengekspor data. Silakan coba lagi.");
                                }
                            });
                        }); 
                        function toggleNominal() {
                            var isFree = document.getElementById('isFree');
                            var nominalInput = document.getElementById('nominalInput');
                            
                            if (isFree.checked) {
                                nominalInput.value = '0';
                                nominalInput.disabled = true;
                            } else {
                                nominalInput.value = '';
                                nominalInput.disabled = false;
                            }
                        }
                        

                    $('#selectAll').click(function() {
                    $('input[type="checkbox"]').prop('checked', this.checked);
                });

               
                document.getElementById('oneTimeUse').addEventListener('change', function() {
        localStorage.setItem('oneTimeUseChecked', this.checked);
    });

    // Ambil status checkbox dari localStorage saat halaman dimuat
    window.onload = function() {
        var isChecked = localStorage.getItem('oneTimeUseChecked') === 'true';
        document.getElementById('oneTimeUse').checked = isChecked;
    };

    // Ketika modal dibuka, set checkbox 'Sekali Pakai' untuk tercentang
    $('#manualVoucherModal').on('show.bs.modal', function () {
        document.getElementById('oneTimeUse').checked = true; // Set checkbox menjadi tercentang
    });

        </script>
    </body>
</html>