<?php
require_once 'function.php';

// Check if user is logged in as admin
if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'true') {
    header('location:login.php');
    exit;
}

// Function to get setting value
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

// Function to update setting
function updateSetting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->bind_param("ss", $value, $key);
    return $stmt->execute();
}

// Tambahkan fungsi untuk update success PIN
function updateSuccessPin($pin) {
    global $conn;
    $stmt = $conn->prepare("UPDATE success_pin SET pin = ? WHERE id = 1");
    $stmt->bind_param("s", $pin);
    return $stmt->execute();
}

// Tambahkan fungsi untuk mendapatkan success PIN
function getSuccessPin() {
    global $conn;
    $stmt = $conn->prepare("SELECT pin FROM success_pin WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['pin'];
    }
    return null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
    $message = '';

    // Update Midtrans settings
    if (isset($_POST['update_midtrans'])) {
        $server_key = $_POST['midtrans_server_key'];
        $client_key = $_POST['midtrans_client_key'];
        $is_production = isset($_POST['midtrans_is_production']) ? '1' : '0';

        $success = updateSetting('midtrans_server_key', $server_key) &&
                  updateSetting('midtrans_client_key', $client_key) &&
                  updateSetting('midtrans_is_production', $is_production);
        
        $message = $success ? 'Midtrans settings updated successfully!' : 'Failed to update Midtrans settings.';
    }

    // Update admin credentials
    if (isset($_POST['update_admin'])) {
        $email = $_POST['admin_email'];
        $password = $_POST['admin_password'];

        $success = updateSetting('admin_email', $email) &&
                  updateSetting('admin_password', $password);
        
        $message = $success ? 'Admin credentials updated successfully!' : 'Failed to update admin credentials.';
    }

    // Update keypad PIN
    if (isset($_POST['update_pin'])) {
        $pin = $_POST['keypad_pin'];
        if (strlen($pin) === 4 && is_numeric($pin)) {
            $success = updateSetting('keypad_pin', $pin);
            $message = $success ? 'Keypad PIN updated successfully!' : 'Failed to update keypad PIN.';
        } else {
            $success = false;
            $message = 'PIN must be exactly 4 digits.';
        }
    }

    // Update success page PIN
    if (isset($_POST['update_success_pin'])) {
        $success_pin = $_POST['success_page_pin'];
        if (strlen($success_pin) === 4 && is_numeric($success_pin)) {
            $success = updateSetting('success_page_pin', $success_pin);
            $message = $success ? 'Success page PIN updated successfully!' : 'Failed to update success page PIN.';
        } else {
            $success = false;
            $message = 'Success page PIN must be exactly 4 digits.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_background'])) {
    if (isset($_FILES['background_file']) && $_FILES['background_file']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'assets/backgrounds/'; // Pastikan direktori ini ada
        $uploadFile = $uploadDir . basename($_FILES['background_file']['name']);
        
        // Pindahkan file yang diupload
        if (move_uploaded_file($_FILES['background_file']['tmp_name'], $uploadFile)) {
            // Update pengaturan latar belakang
            $backgroundType = $_POST['background_type'];
            updateBackgroundSetting($uploadFile, $backgroundType); // Fungsi untuk memperbarui pengaturan
            echo "<p>Background updated successfully!</p>";
        } else {
            echo "<p>Error uploading file.</p>";
        }
    }
}

// Fungsi untuk memperbarui pengaturan latar belakang
function updateBackgroundSetting($path, $type) {
    $settings = json_decode(file_get_contents('config/background.json'), true);
    $settings['path'] = $path; // Update path
    $settings['type'] = $type; // Update type
    file_put_contents('config/background.json', json_encode($settings));
}
$backgroundConfig = json_decode(file_get_contents('config/background.json'), true);
?>

<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Setting</title>
    <link href="css/style.css" rel="stylesheet" />
    <link href="css/styleS.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>          
</head>
<body class="sb-nav-fixed">
<?php if (isset($backgroundConfig['path'])): ?>
    <div class="background-container">
        <?php if ($backgroundConfig['type'] === 'image'): ?>
            <img src="<?php echo htmlspecialchars($backgroundConfig['path']); ?>" alt="Background" style="width: 100%; height: auto;">
        <?php else: ?>
            <video autoplay muted loop playsinline style="width: 100%; height: auto;">
                <source src="<?php echo htmlspecialchars($backgroundConfig['path']); ?>" type="video/mp4">
            </video>
        <?php endif; ?>
        <div class="background-overlay"></div>
    </div>
<?php endif; ?>

<div class="container">
    <h1>Product List</h1>
    <div class="product-list">
        <?php
        // Ambil data produk dari database
        $produk = mysqli_query($conn, "SELECT * FROM products WHERE visible = 1");
        while ($item = mysqli_fetch_assoc($produk)) {
            echo '<div class="product">';
            echo '<h2>' . htmlspecialchars($item['name']) . '</h2>';
            echo '<p>Price: Rp ' . number_format($item['price'], 0, ',', '.') . '</p>';
            echo '<p>' . htmlspecialchars($item['description']) . '</p>';
            echo '<button onclick="showPaymentModal(' . $item['id'] . ', \'' . htmlspecialchars($item['name']) . '\', ' . $item['price'] . ')">Buy</button>';
            echo '</div>';
        }
        ?>
    </div>
</div>

    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark  ">
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
                    <h1>Settings Management</h1>
                    
                    <!-- <?php if (isset($message)): ?>
                        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?> -->

                    <!-- Midtrans Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Midtrans Configuration</h4>
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
                                    <label class="form-check-label">Production Mode</label>
                                </div>
                                <button type="submit" name="update_midtrans" class="btn btn-dark mr-2">Update Midtrans Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- Admin Credentials -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Admin Credentials</h4>
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
                                           placeholder="Enter new password" required>
                                </div>
                                <button type="submit" name="update_admin" class="btn btn-dark mr-2">Update Admin Credentials</button>
                            </form>
                        </div>
                    </div>

                    <!-- Keypad PIN -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Keypad PIN</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">PIN Access (4 digits)</label>
                                    <input type="text" class="form-control" name="keypad_pin" pattern="[0-9]{4}" 
                                           value="<?php echo htmlspecialchars(getSetting('keypad_pin')); ?>" 
                                           maxlength="4" required>
                                </div>
                                <button type="submit" name="update_pin" class="btn btn-dark mr-2">Update PIN</button>
                            </form><br>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">PIN Success Page (4 digits)</label>
                                    <input type="text" class="form-control" name="success_page_pin" pattern="[0-9]{4}" 
                                           value="<?php echo htmlspecialchars(getSetting('success_page_pin')); ?>" 
                                           maxlength="4" required>
                                </div>
                                <button type="submit" name="update_success_pin" class="btn btn-dark mr-2">Update PIN</button>
                            </form>
                        </div>
                    </div>
                    <!-- Background Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Background Settings</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Background Type</label>
                                    <select name="background_type" class="form-control">
                                        <option value="image" <?php echo getSetting('background_type') == 'image' ? 'selected' : ''; ?>>Image</option>
                                        <option value="video" <?php echo getSetting('background_type') == 'video' ? 'selected' : ''; ?>>Video</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Upload Background File</label>
                                    <input type="file" class="form-control" name="background_file" 
                                        accept="image/*,video/*">
                                </div>
                                <button type="submit" name="update_background" class="btn btn-dark mr-2">Update Background</button>
                            </form>
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
</body>
</html>