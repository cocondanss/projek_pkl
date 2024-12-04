<?php
/**
 * List Product Page
 * File: listproduct.php
 * Fungsi: Menampilkan daftar produk dan mengelola sistem voucher
 */

require 'function.php';

/**
 * Fungsi untuk menerapkan voucher pada harga produk
 * @param string $voucherCode - Kode voucher yang diinput
 * @param float $price - Harga asli produk
 * @return float - Harga setelah penerapan voucher
 */
function applyVoucher($voucherCode, $price) {
    global $conn;

    // Persiapkan dan eksekusi query untuk mendapatkan voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek apakah voucher ditemukan
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Cek apakah voucher sekali pakai dan sudah digunakan
        if ($row['one_time_use'] == 1 && $row['used_at'] !== null) {
            return $price; // Kembalikan harga asli jika voucher sudah digunakan
        }
        
        $discountAmount = $row['discount_amount'];

        // Hitung harga setelah diskon
        if ($discountAmount <= 100) { // Jika diskon dalam persentase
            $discountedPrice = $price - ($price * ($discountAmount / 100));
        } else { // Jika diskon dalam nominal
            $discountedPrice = $price - $discountAmount;
        }

        return max(0, $discountedPrice); // Pastikan harga tidak negatif
    }

    return $price; // Kembalikan harga asli jika voucher tidak valid
}

// Inisialisasi variabel untuk sistem voucher
$voucherMessages = [];
$voucherCode = '';
$originalPrice = 0;
$discountedPrice = 0;

// Proses pengecekan voucher saat ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    $voucherCode = trim($_POST['voucher_code']);
    
    // Validasi voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Cek apakah voucher sudah digunakan
        if ($row['one_time_use'] == 1 && $row['used_at'] !== null) {
            $voucherMessages[] = "<p class='voucher-message error'>Voucher sudah digunakan.</p>";
            $discountedPrice = $originalPrice;
            $voucherCode = ''; // Reset voucher code
        } else {
            // Update waktu penggunaan untuk voucher sekali pakai
            if ($row['one_time_use'] == 1) {
                date_default_timezone_set('Asia/Jakarta');
                $currentDateTime = date('Y-m-d H:i:s');
                
                $updateStmt = $conn->prepare("UPDATE vouchers2 SET used_at = ? WHERE code = ?");
                $updateStmt->bind_param("ss", $currentDateTime, $voucherCode);
                $updateStmt->execute();
            }
            
            $voucherMessages[] = "<p class='voucher-message success'>Voucher berhasil digunakan.</p>";
        }
    } else {
        $voucherMessages[] = "<p class='voucher-message error'>Voucher tidak valid.</p>";
        $discountedPrice = $originalPrice;
    }
}

// Ambil data produk yang visible
$produk = mysqli_query($conn, "SELECT * FROM products WHERE visible = 1");
if (!$produk) {
    die("Query gagal: " . mysqli_error($conn));
}

// Mulai output buffering untuk request AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    ob_start();
}
?>