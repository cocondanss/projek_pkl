<?php
require 'function.php';

/**
 * Fungsi untuk menerapkan voucher pada harga produk
 * @param string $voucherCode Kode voucher yang akan digunakan
 * @param float $price Harga asli produk
 * @return float Harga setelah diskon
 */
function applyVoucher($voucherCode, $price) {
    global $conn;
    
    $debug_info = "Voucher Code: $voucherCode, Original Price: $price\n";

    // Persiapkan query untuk mencari voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $debug_info .= "Voucher found: " . print_r($row, true) . "\n";
        $discountAmount = $row['discount_amount'];
        
        // Tentukan jenis diskon (persentase atau nominal)
        if ($discountAmount <= 100) {
            // Diskon persentase
            $discountedPrice = $price - ($price * ($discountAmount / 100));
        } else {
            // Diskon nominal
            $discountedPrice = $price - $discountAmount;
        }
        
        // Pastikan harga tidak negatif
        return max($discountedPrice, 0);
    }

    // Jika voucher tidak ditemukan, kembalikan harga asli
    return $price;
}

// Inisialisasi variabel untuk pesan voucher
$voucherMessages = [];
$voucherCode = '';

// Proses penggunaan voucher jika ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    $voucherCode = $_POST['voucher_code'];
    
    // Validasi voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Cek apakah voucher one-time use dan sudah digunakan
        if ($row['one_time_use'] == 1 && $row['used_at'] !== null) {
            $voucherMessages[] = "<p class='voucher-message error'>Voucher hanya dapat digunakan sekali</p>";
        } else {
            // Update status voucher
            date_default_timezone_set('Asia/Jakarta');
            $currentDateTime = date('Y-m-d H:i:s');
            $updateStmt = $conn->prepare("UPDATE vouchers2 SET used_at = ? WHERE code = ?");
            $updateStmt->bind_param("ss", $currentDateTime, $voucherCode);
            $updateStmt->execute();
            
            $voucherMessages[] = "<p class='voucher-message success'>Voucher berhasil digunakan.</p>";
        }
    } else {
        $voucherMessages[] = "<p class='voucher-message error'>Voucher tidak valid.</p>";
    }
}

// Ambil daftar produk yang visible
$produk = mysqli_query($conn, "SELECT * FROM products WHERE visible = 1 ");
if (!$produk) {
    die("Query gagal: " . mysqli_error($conn));
}

// Jika request AJAX, mulai output buffering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    ob_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags dan CSS -->
    <?php include 'includes/header.php'; ?>
    <style>
        /* CSS styles... */
    </style>
</head>
<body>
    <!-- Struktur HTML -->
    <div class="container-index">
        <!-- Header dan konten -->
        <?php include 'includes/content.php'; ?>
        
        <!-- Daftar produk -->
        <div class="product-list">
            <?php foreach ($produk as $item): 
                $originalPrice = $item['price'];
                $discountedPrice = applyVoucher($voucherCode, $originalPrice);
            ?>
                <!-- Template produk -->
                <?php include 'includes/product-template.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Form voucher -->
        <?php include 'includes/voucher-form.php'; ?>
    </div>

    <!-- Modal-modal -->
    <?php include 'includes/modals.php'; ?>

    <!-- Scripts -->
    <?php include 'includes/scripts.php'; ?>
    
    <script>
        // JavaScript functions...
        // (Semua fungsi JavaScript tetap sama, hanya ditambahkan komentar)
    </script>
</body>
</html>