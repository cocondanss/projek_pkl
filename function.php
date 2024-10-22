<?php
session_start();
//koneksi ke database
$conn =mysqli_connect("localhost","root","","framee");
//if($conn){
//    echo 'berhasil';
//

// Midtrans configuration
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-BiPEZ8YxMZheywHq49sAQthl');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-uJgC77ydf09Kgatf');

// Set true for production environment
define('IS_PRODUCTION', false);

require_once 'vendor/autoload.php';

\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = IS_PRODUCTION;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;


// Add this query to create 'visible' column if it doesn't exist
$alter_table_query = "ALTER TABLE products ADD COLUMN IF NOT EXISTS visible TINYINT(1) DEFAULT 1";
mysqli_query($conn, $alter_table_query);


//menambah user baru
    if (isset($_POST['TambahUser'])) {
        $namauser = $_POST['namauser'];
        $status = $_POST['status'];
        $umur = $_POST['umur'];

    $addtotable = mysqli_query($conn,"insert into user (namauser, status, umur) values('$namauser','$status','$umur')");

    if($addtotable){
        header("location:user.php");
    } else{
        echo 'Gagal';
        header('location:user.php');
    }
}


// Fungsi untuk menambah voucher
if (isset($_POST['TambahVoucher'])) {
    $code_prefix = $_POST['code_prefix'];
    $is_used = $_POST['is_used'];
    $voucher_count = $_POST['voucher_count'];

    for ($i = 0; $i < $voucher_count; $i++) {
        $random_number = mt_rand(1000000000, 9999999999);
        $unique_code = $code_prefix . '' . $random_number;
        $addtotable = mysqli_query($conn, "insert into vouchers (code, is_used) values('$unique_code', '$is_used')");
    }

    if ($addtotable) {
        header("location:voucher.php");
    } else {
        echo 'Gagal';
        header('location:voucher.php');
    }
}

    // Tambahkan kode ini setelah proses tambah voucher selesai
    if (isset($_POST['simpan_ekspor'])) {
    // Kode untuk menyimpan data ke database Anda
    $code_prefix = $_POST['code_prefix'];
    $voucher_count = $_POST['voucher_count'];
  
    for ($i=0; $i < $voucher_count; $i++) { 
      $random_number = mt_rand(1000000000, 9999999999);
      $unique_code = $code_prefix . '' . $random_number;
      $addtotable = mysqli_query($conn,"insert into vouchers (code, is_used) values('$unique_code','0')");
    }
  
    // Kode untuk mengekspor data ke file teks
    $fileName = 'voucher_data_' . date('d-m-Y') . '.txt';
    $fileContent = '' . "";
    $fileContent .= 'Daftar Voucher: ' . "\n";
    $fileContent .= '====================================' . "\n";
    $fileContent .= 'Kode Voucher | Jumlah Diskon | Status | Tanggal Digunakan' . "\n";
    $fileContent .= '------------------------------------' . "\n";
  
    $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");
    while($data = mysqli_fetch_array($ambilsemuadatavoucher)){
      $code = $data['code'];
      $status = ($data['is_used'] == 0) ? 'Belum Digunakan' : 'Sudah Digunakan';
      $used_at = $data['used_at'] ? $data['used_at'] : '-';
      $fileContent .= $code . ' | ' . $status . ' | ' . $used_at . "\n";
    }

        // Kode untuk mengunduh file yang diekspor
        $file = fopen($fileName, 'w');
        fwrite($file, $fileContent);
        fclose($file);
      
        // Kode untuk mengunduh file secara otomatis
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($fileName));
        readfile($fileName);
        exit;
    }

//menambah transaksi
if (isset($_POST['TambahTransaksi'])) {
    $product_name = $_POST['product_name'];
    $price= $_POST['price'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];

$addtotable = mysqli_query($conn,"insert into transaksi (product_name, price, tanggal, status) values('$product_name','$price','$tanggal','$status')");

if($addtotable){
    header("location:transaksi.php");
} else{
    echo 'Gagal';
    header('location:transaksi.php');
}
}

//menambah produk
if (isset($_POST['TambahProduk'])) {
    $name = $_POST['name'];
    $discount = $_POST['discount'];
    $price = $_POST['price'];

$addtotable = mysqli_query($conn,"insert into products (name, price, discount) values('$name','$price','$discount')");

if($addtotable){
    header("location:index.php");
    exit();
} else{
    echo 'Gagal';
    header('location:index.php');
    exit();
}
}


//update info user
if(isset($_POST['updateuser'])){
    $idu = $_POST['idu'];
    $namauser = $_POST['namauser'];
    $status = $_POST['status'];
    $umur = $_POST['umur'];

    $update = mysqli_query($conn,"update user set namauser='$namauser', status='$status', umur='$umur' where iduser ='$idu'");
    if($update){
        header("location:user.php");
    } else{
        echo 'Gagal';
        header('location:user.php');
    }
}






//hapus user
if (isset($_POST['hapususer'])) {
    $idu = $_POST['idu'];

    $hapus = mysqli_query($conn,"delete from user where iduser='$idu'");
    if($hapus){
        header("location:user.php");
    } else{
        echo 'Gagal';
        header('location:user.php');
    }

}

//edit transaksi



//hapus transaksi
if (isset($_POST['hapustransaksi'])) {
    $idt = $_POST['idt'];

    $hapust = mysqli_query($conn,"delete from transaksi where product_id='$idt'");
    if($hapust){
        header("location:transaksi.php");
    } else{
        echo 'Gagal';
        header('location:transaksi.php');
    }

}

// Hapus voucher terpilih
if (isset($_POST['hapusvoucher'])) {
    if(isset($_POST['delete'])) {
        foreach($_POST['delete'] as $id) {
            $id = mysqli_real_escape_string($conn, $id);
            $hapusv = mysqli_query($conn, "DELETE FROM vouchers WHERE id='$id'");
        }
        if($hapusv){
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


if (isset($_POST['simpan_ekspor'])) {
    // Kode untuk menyimpan data ke database Anda
    $code_prefix = $_POST['code_prefix'];
    $discount_amount = $_POST['discount_amount'];
    $voucher_count = $_POST['voucher_count'];
  
    for ($i=0; $i < $voucher_count; $i++) { 
      $random_number = mt_rand(1000000000, 9999999999);
      $unique_code = $code_prefix . '' . $random_number;
      $addtotable = mysqli_query($conn,"insert into vouchers (code, discount_amount, is_used) values('$unique_code','$discount_amount','0')");
    }
  
    // Kode untuk mengekspor data ke file teks
    $fileName = 'voucher_data_' . date('d-m-Y') . '.txt';
    $fileContent = '' . "";
    $fileContent .= 'Daftar Voucher: ' . "\n";
  
    $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");
    while($data = mysqli_fetch_array($ambilsemuadatavoucher)){
      $code = $data['code'];
      $fileContent .= $code . "\n";
    }
  
    // Kode untuk mengunduh file yang diekspor
    $file = fopen($fileName, 'w');
    fwrite($file, $fileContent);
    fclose($file);
  
    echo '<a href="' . $fileName . '" download="' . $fileName . '">Unduh File</a>';
    exit;
  }

  //edit voucher
    if(isset($_POST['updatevoucher'])){
    $id = $_POST['id'];
    $code_prefix = $_POST['code_prefix'];
    $discount_amount = $_POST['discount_amount'];
    $is_used = $_POST['is_used'];

    $updatev = mysqli_query($conn,"update vouchers set code_prefix='$code_prefix', discount_amount='$discount_amount', is_used='$is_used' where id ='$id'");
    if($updatev){
        header("location:voucher.php");
    } else{
        echo 'Gagal';
        header('location:voucher.php');
    }
}

//hapus voucher yang sudah digunakan
if (isset($_POST['hapus_voucher_digunakan'])) {
    $hapus_voucher = mysqli_query($conn, "DELETE FROM vouchers WHERE is_used = 1");
    if ($hapus_voucher) {
        header("location:voucher.php");
    } else {
        echo 'Gagal menghapus voucher';
        header('location:voucher.php');
    }
}


// Fetch products from database
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
$products = mysqli_query($conn,"select * from products");