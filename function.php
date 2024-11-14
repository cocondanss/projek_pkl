<?php
session_start();


// Konfigurasi koneksi database
$conn = mysqli_connect("localhost", "u529472640_root", "Daclen123", "u529472640_framee");


// Konfigurasi Midtrans
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-BiPEZ8YxMZheywHq49sAQthl');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-uJgC77ydf09Kgatf');
define('IS_PRODUCTION', false);

// Inisialisasi Midtrans
require_once 'vendor/autoload.php';
\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = IS_PRODUCTION;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Fungsi untuk mengupdate tampilan detail produk
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $produk = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
    $row = mysqli_fetch_assoc($produk);
    
    echo '<div class="bg-blue-900 text-white p-4 rounded-lg">';
    echo '<h2 class="text-lg font-bold">' . $row['name'] . '</h2>';
    echo '<p class="text-md">Rp ' . $row['price'] . '</p>';
    echo '<p class="mt-2 text-sm">' . $row['desc'] . '</p>';
    echo '<button class="bg-gray-300 text-blue-900 py-1 px-3 rounded-full mt-4" onclick="handleBuy(' . $row['name'] . ', \'' . $row['price'] . '\', ' . $row['desc'] . ')">Buy</button>';
    echo '</div>';
}

// Menambah kolom 'visible' jika belum ada
$alter_table_query = "ALTER TABLE products ADD COLUMN IF NOT EXISTS visible TINYINT(1) DEFAULT 1";
mysqli_query($conn, $alter_table_query);

// Fungsi untuk menambah user baru
if (isset($_POST['TambahUser'])) {
    $namauser = $_POST['namauser'];
    $status = $_POST['status'];
    $umur = $_POST['umur'];

    $addtotable = mysqli_query($conn, "INSERT INTO user (namauser, status, umur) VALUES ('$namauser', '$status', '$umur')");
    
    if ($addtotable) {
        header("location:user.php");
    } else {
        echo 'Gagal';
        header('location:user.php');
    }
}

// Fungsi untuk menambah voucher baru
if (isset($_POST['TambahVoucher'])) {
    $code_prefix = $_POST['code_prefix'];
    $discount_amount = $_POST['discount_amount'];
    $voucher_count = $_POST['voucher_count'];
    $vouchers = []; // Array untuk menyimpan voucher yang berhasil ditambahkan

    for ($i = 0; $i < $voucher_count; $i++) {
        $random_number = mt_rand(1000000000, 9999999999);
        $unique_code = $code_prefix . $random_number;
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $claimed_at = $date->format('Y-m-d H:i:s');
    
        // Update status voucher menjadi digunakan dan simpan waktu klaim
        $update_query = "UPDATE vouchers SET status = 'Digunakan', claimed_at = '$claimed_at' WHERE code = '$voucher_code'";
    
        if (mysqli_query($conn, $update_query)) {
            echo "Voucher berhasil diklaim pada: " . $claimed_at; // Tampilkan waktu klaim
        } else {
            echo "Gagal mengklaim voucher: " . mysqli_error($conn);
        }
    }

    // Jika semua voucher berhasil ditambahkan
    if (count($vouchers) === $voucher_count) {
        header("location:voucher.php");
        exit();
    } else {
        // Jika tidak semua voucher berhasil ditambahkan
        header('location:voucher.php');
        exit();
    }
}
if (isset($_POST['TambahVoucherManual'])) {
    $manual_code = trim($_POST['manual_code']);
    $is_free = isset($_POST['is_free']) ? 1 : 0; // Cek apakah voucher gratis
    $nominal = $is_free ? 0 : (int)$_POST['nominal']; // Jika gratis, nominal harus 0

    // Validasi input
    if ($is_free == 0 && empty($_POST['nominal'])) {
        $_SESSION['error'] = 'Nominal harus diisi jika voucher tidak gratis.';
        header('location:voucher.php');
        exit();
    }

    // Cek apakah kode voucher sudah ada
    $checkQuery = mysqli_query($conn, "SELECT * FROM vouchers2 WHERE code = '$manual_code'");
    if (mysqli_num_rows($checkQuery) > 0) {
        $_SESSION['error'] = 'Kode voucher sudah ada. Silakan gunakan kode yang lain.';
        header('location:voucher.php');
        exit();
    }
    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
    $created_at = $date->format('Y-m-d H:i:s');

    // Menyimpan voucher ke database
    $addtotable = mysqli_query($conn, "INSERT INTO vouchers2 (code, discount_amount, created_at, is_free) VALUES ('$manual_code', '$nominal', '$created_at', '$is_free')");

    if ($addtotable) {
        $_SESSION['message'] = 'Voucher berhasil ditambahkan!';
        header("location:voucher.php");
        exit();
    } else {
        $_SESSION['error'] = 'Gagal menambahkan voucher: ' . mysqli_error($conn);
        header('location:voucher.php');
        exit();
    }
}

// Menampilkan pesan sukses atau error di halaman voucher.php
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']); // Hapus pesan setelah ditampilkan
}

// Fungsi untuk update tabel voucher
if (isset($_POST['action']) && $_POST['action'] == 'update_tabel_voucher') {
    $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers2");
    $i = 1;
    $html = '';
    
    while ($data = mysqli_fetch_array($ambilsemuadatavoucher)) {
        $code = $data['code'];
        $discount_amount = $data['discount_amount'];
        $is_used = $data['is_used'];
        $created_at = $data['created_at'];
        $used_at = $data['used_at'];
        
        $status = ($is_used == 1) ? "Sudah digunakan" : "Belum digunakan";
        
        $html .= "<tr>";
        $html .= "<td>" . $i++ . "</td>";
        $html .= "<td>" . $code . "</td>";
        $html .= "<td>" . $discount_amount . "</td>";
        $html .= "<td>" . $status . "</td>";
        $html .= "<td>" . $created_at . "</td>";
        $html .= "<td>" . ($used_at ? $used_at : '-') . "</td>";
        $html .= "</tr>";
    }
    echo $html;
}

// Fungsi untuk menambah transaksi
if (isset($_POST['TambahTransaksi'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];

    $addtotable = mysqli_query($conn, "INSERT INTO transaksi (product_name, price, tanggal, status) VALUES ('$product_name', '$price', '$tanggal', '$status')");

    if ($addtotable) {
        header("location:transaksi.php");
    } else {
        echo 'Gagal';
        header('location:transaksi.php');
    }
}

// Fungsi untuk menambah produk
if (isset($_POST['TambahProduk'])) {
    $name = $_POST['name'];
    $deskripsi = $_POST['deskripsi'];
    $price = $_POST['price'];
    $id = $_POST['id'];
    $content = $_POST['content'];

    $addtotable = mysqli_query($conn, "INSERT INTO products3 (name, deskripsi, price, id, content) VALUES ('$name', '$deskripsi', '$price', '$id', '$content')");

    if ($addtotable) {
        header("location:produk.php");
    } else {
        echo 'Gagal';
        header('location:produk.php');
    }
}

// Fungsi untuk hapus voucher terpilih
if (isset($_POST['hapusvoucher'])) {
    if (isset($_POST['delete'])) {
        foreach ($_POST['delete'] as $id) {
            $id = mysqli_real_escape_string($conn, $id);
            $hapusv = mysqli_query($conn, "DELETE FROM vouchers2 WHERE id='$id'");
        }
        
        if ($hapusv) {
            header("location:voucher.php");
        } else {
            echo 'Gagal menghapus voucher';
            header('location:voucher.php');
        }
    } else {
        echo 'Tidak ada voucher yang dipilih';
        header('location:voucher.php');
    }
}

// Fungsi untuk update informasi user
if (isset($_POST['updateuser'])) {
    $idu = $_POST['idu'];
    $namauser = $_POST['namauser'];
    $status = $_POST['status'];
    $umur = $_POST['umur'];

    $update = mysqli_query($conn, "UPDATE user SET namauser='$namauser', status='$status', umur='$umur' WHERE iduser='$idu'");
    
    if ($update) {
        header("location:user.php");
    } else {
        echo 'Gagal';
        header('location:user.php');
    }
}

// Fungsi untuk menghapus user
if (isset($_POST['hapususer'])) {
    $idu = $_POST['idu'];
    
    $hapus = mysqli_query($conn, "DELETE FROM user WHERE iduser='$idu'");
    
    if ($hapus) {
        header("location:user.php");
    } else {
        echo 'Gagal';
        header('location:user.php');
    }
}

// Fungsi untuk edit produk
if (isset($_POST['updatebarang'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $discount = $_POST['discount'];
    $price = $_POST['price'];
    $stok = $_POST['stok'];

    $updatep = mysqli_query($conn, "UPDATE products SET name='$name', discount='$discount', price='$price', stok='$stok' WHERE id='$id'");
    
    if ($updatep) {
        header("location:produk.php");
    } else {
        echo 'Gagal';
        header('location:produk.php');
    }
}

// Fungsi untuk hapus produk
if (isset($_POST['hapusbarang'])) {
    $id = $_POST['id'];

    $hapusp = mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    
    if ($hapusp) {
        header("location:produk.php");
    } else {
        echo 'Gagal';
        header('location:produk.php');
    }
}

// Fungsi untuk hapus transaksi terpilih
if (isset($_POST['hapustransaksi'])) {
    if (isset($_POST['delete'])) {
        foreach ($_POST['delete'] as $order_id) {
            $order_id = mysqli_real_escape_string($conn, $order_id);
            $hapust = mysqli_query($conn, "DELETE FROM transaksi WHERE order_id='$order_id'");
        }
        
        if ($hapust) {
            header("location:transaksi.php");
        } else {
            echo 'Gagal menghapus transaksi';
            header('location:transaksi.php');
        }
    } else {
        echo 'Tidak ada transaksi yang dipilih';
        header('location:transaksi.php');
    }
}

// Fungsi untuk menyimpan dan mengekspor voucher ke file
if (isset($_POST['simpan_ekspor'])) {
    $code_prefix = $_POST['code_prefix'];
    $discount_amount = $_POST['discount_amount'];
    $voucher_count = $_POST['voucher_count'];

    for ($i = 0; $i < $voucher_count; $i++) {
        $random_number = mt_rand(1000000000, 9999999999);
        $unique_code = $code_prefix . $random_number;
        $addtotable = mysqli_query($conn, "INSERT INTO vouchers (code, discount_amount, is_used) VALUES ('$unique_code', '$discount_amount', '0')");
    }

    $fileName = 'voucher_data_' . date('d-m-Y') . '.txt';
    $fileContent = "Daftar Voucher:\n";

    $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");
    while ($data = mysqli_fetch_array($ambilsemuadatavoucher)) {
        $fileContent .= $data['code'] . "\n";
    }

    $file = fopen($fileName, 'w');
    fwrite($file, $fileContent);
    fclose($file);

    echo '<a href="' . $fileName . '" download="' . $fileName . '">Unduh File</a>';
    exit;
}

// Fungsi untuk hapus voucher yang sudah digunakan
if (isset($_POST['hapus_voucher_digunakan'])) {
    $hapus_voucher = mysqli_query($conn, "DELETE FROM vouchers2 WHERE is_used = 1");
    
    if ($hapus_voucher) {
        header("location:voucher.php");
    } else {
        echo 'Gagal menghapus voucher';
        header('location:voucher.php');
    }
}

// Mengambil data produk
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
$products = mysqli_query($conn, "SELECT * FROM products");