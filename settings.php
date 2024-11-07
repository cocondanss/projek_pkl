<?php
require_once 'function.php';

// Memeriksa status login admin
if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'true') {
    header('location:login.php');
    exit;
}

/**
 * Fungsi untuk mengambil nilai setting dari database
 * @param string $key Kunci setting yang akan diambil
 * @return string|null Nilai setting atau null jika tidak ditemukan
 */
function getSetting($key) {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return null;
}

/**
 * Fungsi untuk memperbarui nilai setting di database
 * @param string $key Kunci setting yang akan diupdate
 * @param string $value Nilai baru untuk setting
 * @return bool Status keberhasilan update
 */
function updateSetting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->bind_param("ss", $value, $key);
    return $stmt->execute();
}

// Menangani submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
    $message = '';

    // Memperbarui pengaturan Midtrans
    if (isset($_POST['update_midtrans'])) {
        $server_key = $_POST['midtrans_server_key'];
        $client_key = $_POST['midtrans_client_key'];
        $is_production = isset($_POST['midtrans_is_production']) ? '1' : '0';

        $success = updateSetting('midtrans_server_key', $server_key) &&
                  updateSetting('midtrans_client_key', $client_key) &&
                  updateSetting('midtrans_is_production', $is_production);
        
        $message = $success ? 'Pengaturan Midtrans berhasil diperbarui!' : 'Gagal memperbarui pengaturan Midtrans.';
    }

    // Memperbarui kredensial admin
    if (isset($_POST['update_admin'])) {
        $email = $_POST['admin_email'];
        $password = $_POST['admin_password'];

        $success = updateSetting('admin_email', $email) &&
                  updateSetting('admin_password', $password);
        
        $message = $success ? 'Kredensial admin berhasil diperbarui!' : 'Gagal memperbarui kredensial admin.';
    }

    // Memperbarui PIN keypad
    if (isset($_POST['update_pin'])) {
        $pin = $_POST['keypad_pin'];
        if (strlen($pin) === 4 && is_numeric($pin)) {
            $success = updateSetting('keypad_pin', $pin);
            $message = $success ? 'PIN Keypad berhasil diperbarui!' : 'Gagal memperbarui PIN Keypad.';
        } else {
            $success = false;
            $message = 'PIN harus terdiri dari 4 digit angka.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pengaturan Sistem</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"></script>
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

    /* Enhanced card styling */
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 20px 25px;
    }

    .card-header h4 {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .card-body {
        padding: 25px;
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

    /* Button styling */
    .btn-primary {
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
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

    /* Main content container */
    .container-fluid {
        padding: 25px;
    }

    /* Page title */
    h1 {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 30px;
        font-size: 1.8rem;
    }
</style>

<body class="sb-nav-fixed">
    <!-- Navbar Atas -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Daclen</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#">
            <i class="fas fa-bars"></i>
        </button>
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

        <!-- Konten Utama -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1>Manajemen Pengaturan</h1>
                    
                    <!-- Pesan Alert -->
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Pengaturan Midtrans -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Konfigurasi Midtrans</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Server Key</label>
                                    <input type="text" class="form-control" name="midtrans_server_key" 
                                           value="<?php echo htmlspecialchars(getSetting('midtrans_server_key')); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Client Key</label>
                                    <input type="text" class="form-control" name="midtrans_client_key" 
                                           value="<?php echo htmlspecialchars(getSetting('midtrans_client_key')); ?>" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="midtrans_is_production" 
                                           <?php echo getSetting('midtrans_is_production') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Mode Produksi</label>
                                </div>
                                <button type="submit" name="update_midtrans" class="btn btn-primary">Perbarui Pengaturan Midtrans</button>
                            </form>
                        </div>
                    </div>

                    <!-- Form Kredensial Admin -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Kredensial Admin</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="admin_email" 
                                           value="<?php echo htmlspecialchars(getSetting('admin_email')); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="admin_password" 
                                           placeholder="Masukkan password baru" required>
                                </div>
                                <button type="submit" name="update_admin" class="btn btn-primary">Perbarui Kredensial Admin</button>
                            </form>
                        </div>
                    </div>

                    <!-- Form PIN Keypad -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>PIN Keypad</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">PIN (4 digit)</label>
                                    <input type="text" class="form-control" name="keypad_pin" pattern="[0-9]{4}" 
                                           value="<?php echo htmlspecialchars(getSetting('keypad_pin')); ?>" 
                                           maxlength="4" required>
                                </div>
                                <button type="submit" name="update_pin" class="btn btn-primary">Perbarui PIN</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Hak Cipta &copy; Website Anda 2024</div>
                        <div>
                            <a href="#">Kebijakan Privasi</a>
                            &middot;
                            <a href="#">Syarat &amp; Ketentuan</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- CSS Kustom -->
    <style>
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

    .nav-link.active .sb-nav-link-icon {
        color: #fff;
    }
    </style>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>