<?php
require 'function.php';
require 'cek.php';

date_default_timezone_set('Asia/Jakarta');

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

function validateVoucher($code) {
    global $conn;
    
    // Tambahkan error handling dan logging
    try {
        // Cek voucher berdasarkan kode
        $query = "SELECT * FROM vouchers2 WHERE code = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Error preparing statement: " . $conn->error);
            return ["valid" => false, "message" => "Database error"];
        }

        $stmt->bind_param("s", $code);
        if (!$stmt->execute()) {
            error_log("Error executing statement: " . $stmt->error);
            return ["valid" => false, "message" => "Database error"];
        }

        $result = $stmt->get_result();
        $voucher = $result->fetch_assoc();
        
        if (!$voucher) {
            return ["valid" => false, "message" => "Voucher tidak ditemukan"];
        }
        
        // Cek apakah voucher sudah digunakan dan merupakan voucher sekali pakai
        if ($voucher['one_time_use'] == 1 && $voucher['used_at'] !== null) {
            return ["valid" => false, "message" => "Voucher sudah digunakan"];
        }
        
        return ["valid" => true, "voucher" => $voucher];
    } catch (Exception $e) {
        error_log("Error in validateVoucher: " . $e->getMessage());
        return ["valid" => false, "message" => "Terjadi kesalahan sistem"];
    }
}

function useVoucher($code) {
    global $conn;
    
    try {
        $currentTime = date('Y-m-d H:i:s');
        $query = "UPDATE vouchers2 SET used_at = ? WHERE code = ? AND (used_at IS NULL OR one_time_use = 0)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Error preparing statement: " . $conn->error);
            return false;
        }

        $stmt->bind_param("ss", $currentTime, $code);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Error executing statement: " . $stmt->error);
            return false;
        }

        // Cek apakah ada baris yang terupdate
        if ($stmt->affected_rows === 0) {
            error_log("No rows updated for voucher code: " . $code);
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log("Error in useVoucher: " . $e->getMessage());
        return false;
    }
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
        <link href="css/styleV.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                    <form method="post" action="voucher.php">
                        <h1 class="mt-4">Voucher</h1>
                        <div class="card mb-4">
                            <div class="card-header">
                                <button type="button" class="btn btn-dark mr-2" data-toggle="modal" data-target="#voucherModal">
                                    Tambah Voucher otomatis
                                </button>
                                <button type="button" class="btn btn-dark mr-2" data-toggle="modal" data-target="#manualVoucherModal">
                                    Tambah Voucher Manual
                                </button>
                                <button type="submit" name="hapusvoucher" id="hapusvoucher" class="btn btn-dark mr-2">
                                    Hapus Voucher Terpilih
                                </button>
                                <button type="button" class="btn btn-dark mr-2" id="eksporVoucher">
                                    Ekspor Voucher
                                </button>
                                <!-- <form method="POST" action="voucher.php">
                                    <button type="submit" name="hapusVoucherYangSudahDigunakan" class="btn btn-danger" id="btnHapusVoucher" onclick="return confirm('Apakah Anda yakin ingin menghapus semua voucher yang sudah digunakan?');">
                                        Hapus Voucher Digunakan
                                    </button>
                                </form> -->
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="" width="100%" cellspacing="0">
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
                                                <th>Pilih</th>
                                                <input type="checkbox" id="selectAll" onclick="toggle(this)"> Pilih Semua Voucher<br><br>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php

                                            $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers2");

                                            if (!$ambilsemuadatavoucher) {
                                                die("Query failed: " . mysqli_error($conn)); // Periksa apakah query berhasil
                                            }

                                            $i = 1;
                                            while ($data = mysqli_fetch_array($ambilsemuadatavoucher)) {
                                                $code = $data['code'];
                                                $discount_amount = $data['discount_amount'];
                                                $is_free = $data['is_free'];
                                                $one_time_use = $data['one_time_use'];
                                                $id = $data['id'];
                                                $created_at = $data['created_at']; // UTC
                                                $used_at = $data['used_at']; // UTC

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
                                                    <td><?= htmlspecialchars($status_used); ?></td>
                                                    <td><?= htmlspecialchars($isFreeDisplay); ?></td>
                                                    <td><?= htmlspecialchars($oneTimeUse); ?></td>
                                                    <td>
                                                        <script>
                                                            // Mengonversi waktu UTC ke waktu lokal untuk created_at
                                                            var createdAtUTC = '<?= $created_at; ?>';
                                                            var createdAtLocal = new Date(createdAtUTC + 'Z').toLocaleString('id-ID', { 
                                                                year: 'numeric', 
                                                                month: '2-digit', 
                                                                day: '2-digit', 
                                                                hour: '2-digit', 
                                                                minute: '2-digit', 
                                                                second: '2-digit', 
                                                                hour12: false // untuk format 24 jam
                                                            });

                                                            // Menghapus bagian zona waktu dan mengganti '/' dengan '-'
                                                            createdAtLocal = createdAtLocal.replace(/ GMT.*$/, ''); // Menghapus bagian GMT
                                                            createdAtLocal = createdAtLocal.replace(/\//g, '-'); // Mengganti '/' dengan '-'
                                                            document.write(createdAtLocal);
                                                        </script>
                                                    </td>
                                                    <td><?= !empty($used_at) ? htmlspecialchars(date('d-m-Y H:i:s', strtotime($used_at))) : '-'; ?></td>
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
        
        <div class="modal fade" id="voucherModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Voucher otomatis</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="post">
                        <div class="modal-body">
                            <!-- Kode Voucher -->
                            <div class="form-group">
                                <label for="code_prefix">Kode Voucher:</label>
                                <input type="text" name="code_prefix" class="form-control" value="<?= $voucherCode; ?>" readonly>
                            </div>
                            
                            <!-- Jumlah Voucher -->
                            <div class="form-group">
                                <label>Jumlah Voucher:</label>
                                <input type="number" name="voucher_count" placeholder="Jumlah Voucher" class="form-control" min="1" required>
                            </div>

                            <!-- Radio Buttons -->
                            <div class="form-group">
                                <label>Jenis Voucher:</label>
                                <div class="form-check">
                                    <input type="radio" name="voucherType" value="rupiah" id="rupiahRadio" class="form-check-input">
                                    <label class="form-check-label" for="rupiahRadio">Rupiah</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="voucherType" value="diskon" id="diskonRadio" class="form-check-input">
                                    <label class="form-check-label" for="diskonRadio">Diskon</label>
                                </div>
                            </div>

                            <!-- Input Nominal (initially hidden) -->
                            <div id="nominalInput" style="display: none;" class="form-group">
                                <label for="nominalVoucher">Nominal Voucher (Rupiah):</label>
                                <input type="number" name="nominalVoucher" id="nominalVoucher" class="form-control" min="0" step="1000">
                            </div>

                            <!-- Input Diskon (initially hidden) -->
                            <div id="diskonInput" style="display: none;" class="form-group">
                                <label for="diskonVoucher">Diskon (%):</label>
                                <div class="input-group">
                                    <input type="number" name="diskonVoucher" id="diskonVoucher" class="form-control" min="1" max="100">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark mr-2" name="TambahVoucherOtomatis">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

 <!-- Input Tambah Voucher Manual -->
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
                    <div id="nominalContainer">
                        <input type="number" name="nominal" placeholder="Nominal (Rp)" class="form-control" required><br>
                    </div>

                    <!-- Checkbox for Free Option -->
                    <input type="checkbox" name="is_free" id="isFree" onchange="toggleNominal()"> 
                    <label for="isFree">Gratis</label><br><br>

                    <!-- Checkbox for One-Time Use -->
                    <input type="checkbox" name="one_time_use" id="oneTimeUse"> 
                    <label for="oneTimeUse">Sekali Pakai</label><br><br>

                    <!-- Button to Create Voucher -->
                    <button type="submit" class="btn btn-dark mr-2" name="TambahVoucherManual">Simpan</button>
                </div>
            </form>
        </div>
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
            // Fungsi untuk menampilkan input berdasarkan pilihan radio
            function handleVoucherTypeChange() {
                const rupiahRadio = document.getElementById('rupiahRadio');
                const nominalInput = document.getElementById('nominalInput');
                const diskonInput = document.getElementById('diskonInput');
                
                // Tambahkan event listener untuk setiap radio button
                document.getElementById('rupiahRadio').addEventListener('change', function() {
                    nominalInput.style.display = this.checked ? 'block' : 'none';
                    diskonInput.style.display = 'none';
                    if (this.checked) {
                        document.getElementById('diskonVoucher').value = '';
                    }
                });

                document.getElementById('diskonRadio').addEventListener('change', function() {
                    diskonInput.style.display = this.checked ? 'block' : 'none';
                    nominalInput.style.display = 'none';
                    if (this.checked) {
                        document.getElementById('nominalVoucher').value = '';
                    }
                });
            }

            // Panggil fungsi saat dokumen dimuat
            document.addEventListener('DOMContentLoaded', function() {
                handleVoucherTypeChange();
            });

            // Reset form saat modal ditutup
            $('#voucherModal').on('hidden.bs.modal', function () {
                document.getElementById('nominalVoucher').value = '';
                document.getElementById('diskonVoucher').value = '';
                document.getElementById('rupiahRadio').checked = false;
                document.getElementById('diskonRadio').checked = false;
                document.getElementById('nominalInput').style.display = 'none';
                document.getElementById('diskonInput').style.display = 'none';
            });

        $("#eksporVoucher").click(function(event) {
            event.preventDefault();
            
            // Get all rows from the table
            var rows = document.querySelectorAll('table tbody tr');
            var fileContent = 'Kode Voucher\n';
            
            // Iterate through each row and extract only the voucher code
            rows.forEach(function(row) {
                var cells = row.getElementsByTagName('td');
                if (cells.length > 0) {
                    var code = cells[1].textContent.trim(); // Kode voucher ada di kolom kedua (index 1)
                    fileContent += `${code}\n`;
                }
            });

            // Create and trigger download
            var blob = new Blob([fileContent], {type: 'text/plain'});
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'daftar_voucher.txt';
            link.click();
        });                               

        function toggleNominal() {
        var isFree = document.getElementById('isFree');
        var nominalContainer = document.getElementById('nominalContainer'); // Container untuk input nominal
        var nominalInput = document.getElementsByName('nominal')[0]; // Mengambil input nominal dengan nama

        if (isFree.checked) {
            nominalContainer.style.display = 'none'; // Sembunyikan input nominal
            nominalInput.value = '999999999'; // Mengatur nilai nominal menjadi 0
        } else {
            nominalContainer.style.display = 'block'; // Tampilkan kembali input nominal
            nominalInput.value = ''; // Kosongkan nilai nominal
        }
    }

    // Menambahkan event listener untuk validasi form
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('voucherForm').addEventListener('submit', function(event) {
            const nominalInput = document.getElementsByName('nominal')[0];
            const isFree = document.getElementById('isFree');

            if (!isFree.checked && nominalInput.value.trim() === '') {
                alert('Silakan isi nominal atau centang gratis.');
                event.preventDefault(); // Mencegah form disubmit
            }
        });
    });           




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
