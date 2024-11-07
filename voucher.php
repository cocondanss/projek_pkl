<?php
/**
 * File: voucher.php
 * Deskripsi: Halaman manajemen voucher untuk sistem
 */

require_once 'function.php';
require 'cek.php';

/**
 * Fungsi untuk menghapus voucher yang sudah digunakan
 */
if (isset($_POST['hapusVoucherYangSudahDigunakan'])) {
    $sql = "DELETE FROM vouchers2 WHERE used_at IS NOT NULL AND one_time_use = 1";
    
    if ($conn->query($sql) === TRUE) {
        echo "Voucher yang sudah digunakan dan sekali pakai berhasil dihapus.";
    } else {
        echo "Error: " . $conn->error;
        error_log("Query failed: " . $sql);
    }
}

/**
 * Fungsi untuk menghasilkan kode voucher acak
 * @param int $length Panjang kode voucher yang diinginkan
 * @return string Kode voucher yang dihasilkan
 */
function generateVoucherCode($length = 8) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Inisialisasi kode voucher otomatis
$voucherCode = generateVoucherCode();

/**
 * Handler untuk menambah voucher otomatis
 */
if (isset($_POST['TambahVoucherOtomatis'])) {
    $voucherCode = $_POST['code_prefix'];
    $voucherCount = $_POST['voucher_count'];
    $voucherType = $_POST['voucherType'];
    $nominalVoucher = $_POST['nominalVoucher'];
    $diskonVoucher = $_POST['diskonVoucher'];

    // Menentukan jumlah diskon berdasarkan tipe voucher
    $discountAmount = ($voucherType == 'rupiah') ? $nominalVoucher : $diskonVoucher;

    // Reset auto increment
    mysqli_query($conn, "ALTER TABLE vouchers2 CHANGE id id INT AUTO_INCREMENT;");

    // Generate voucher sesuai jumlah yang diminta
    for ($i = 0; $i < $voucherCount; $i++) {
        $voucherCode = generateVoucherCode();
        mysqli_query($conn, "INSERT INTO vouchers2 (code, discount_amount) VALUES ('$voucherCode', '$discountAmount')");
    }

    header('Location: voucher.php');
}

/**
 * Handler untuk menambah voucher manual
 */
if (isset($_POST['TambahVoucherManual'])) {
    $manualCode = trim(mysqli_real_escape_string($conn, $_POST['manual_code']));
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
    } else {
        header('Location: voucher.php?status=error&message=Gagal menambahkan voucher');
    }
    exit();
}

/**
 * Handler untuk menghapus voucher terpilih
 */
if (isset($_POST['hapusvoucher'])) {
    $id = $_POST['delete'];
    $query = "DELETE FROM vouchers2 WHERE id IN (" . implode(',', $id) . ")";
    mysqli_query($conn, $query);
    header('Location: voucher.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Voucher</title>
    <!-- CSS imports -->
    <link href="css/style.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
<!-- Add this in the <head> section -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<!-- Add this in the <head> section of all three files -->
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
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <form method="post" action="voucher.php">
                        <h1 class="mt-4">Voucher</h1>
                        <div class="card mb-4">
                            <div class="card-header">
                                <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#voucherModal">
                                    Tambah Voucher otomatis
                                </button>
                                <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#manualVoucherModal">
                                    Tambah Voucher Manual
                                </button>
                                <button type="submit" name="hapusvoucher" id="hapusvoucher" class="btn btn-dark mr-2">
                                    Hapus Voucher Terpilih
                                </button>
                                <button type="button" class="btn btn-info mr-2" id="eksporVoucher">
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