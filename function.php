<?php
session_start();

// Konfigurasi koneksi database
$conn = mysqli_connect("localhost", "root", "", "frame");

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

    for ($i = 0; $i < $voucher_count; $i++) {
        $random_number = mt_rand(1000000000, 9999999999);
        $unique_code = $code_prefix . $random_number;
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $created_at = $date->format('Y-m-d H:i:s');

        $addtotable = mysqli_query($conn, "INSERT INTO vouchers (code, discount_amount, created_at) VALUES ('$unique_code', '$discount_amount', '$created_at')");
    }

    if ($addtotable) {
        $vouchers[] = array($unique_code, $discount_amount, 'Belum Digunakan', $created_at);
        header("location:voucher.php");
    } else {
        echo 'Gagal';
        header('location:voucher.php');
    }
}

// Fungsi untuk menambah dan mengekspor voucher
if (isset($_POST['simpanEksporVoucher'])) {
    $code_prefix = $_POST['code_prefix'];
    $discount_amount = $_POST['discount_amount'];
    $voucher_count = $_POST['voucher_count'];
    
    $vouchers = array();
    
    for ($i = 0; $i < $voucher_count; $i++) {
        $random_number = mt_rand(1000000000, 9999999999);
        $unique_code = $code_prefix . $random_number;
        $addtotable = mysqli_query($conn, "INSERT INTO vouchers (code, discount_amount, is_used) VALUES ('$unique_code', '$discount_amount', 0)");
        
        if ($addtotable) {
            $vouchers[] = array($unique_code, $discount_amount, 'Belum Digunakan');
        }
    }

    if (count($vouchers) > 0) {
        $txt_data = "Kode,Jumlah Diskon,Status,Tanggal Dibuat,Tanggal Digunakan\n";
        foreach ($vouchers as $voucher) {
            $txt_data .= implode(',', $voucher) . "\n";
        }

        header('Content-Type: text');
        header('Content-Disposition: attachment; filename="daftar_voucher.txt"');
        echo $txt_data;
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan voucher']);
    }
}

// Fungsi untuk update tabel voucher
if (isset($_POST['action']) && $_POST['action'] == 'update_tabel_voucher') {
    $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");
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