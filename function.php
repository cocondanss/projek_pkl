<?php
session_start();
//koneksi ke database
$conn =mysqli_connect("localhost","u529472640_root","Daclen123","u529472640_framee");
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


// Fungsi untuk mengupdate list produk
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


//menambah voucher
if (isset($_POST['TambahVoucher'])) {
    $code_prefix = $_POST['code_prefix'];
    $discount_amount = $_POST['discount_amount'];
    $is_used = $_POST['is_used'];
    $voucher_count = $_POST['voucher_count'];

for ($i=0; $i < $voucher_count; $i++) { 
    $random_number = mt_rand(1000000000, 9999999999);
    $unique_code = $code_prefix . '' . $random_number;
    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('Asia/Jakarta')); // Atur zona waktu ke Jakarta
    $created_at = $date->format('Y-m-d H:i:s'); // Cetak waktu dalam format Y-m-d H:i:s

    $addtotable = mysqli_query($conn,"insert into vouchers (code, discount_amount, created_at) values('$unique_code','$discount_amount','$created_at')");
}

if($addtotable){
    $vouchers[] = array($unique_code, $discount_amount, 'Belum Digunakan', $created_at);
    header("location:voucher.php");
} else{
    echo 'Gagal';
    header('location:voucher.php');
}
}

    // Menambah voucher dan mengekspor voucher
    if (isset($_POST['simpanEksporVoucher'])) {
        $code_prefix = $_POST['code_prefix'];
        $discount_amount = $_POST['discount_amount'];
        $voucher_count = $_POST['voucher_count'];
    
        $vouchers = array();
    
        for ($i=0; $i < $voucher_count; $i++) { 
            $random_number = mt_rand(1000000000, 9999999999);
            $unique_code = $code_prefix . $random_number;
            $addtotable = mysqli_query($conn, "INSERT INTO vouchers (code, discount_amount, is_used) VALUES ('$unique_code', '$discount_amount', 0)");
            
            if ($addtotable) {
                $vouchers[] = array($unique_code, $discount_amount, 'Belum Digunakan');
            }
        }
    
        if (count($vouchers) > 0) {
            // Prepare txt data
            $txt_data = "Kode,Jumlah Diskon,Status,Tanggal Dibuat,Tanggal Digunakan\n";
            foreach ($vouchers as $voucher) {
                $txt_data .= implode(',', $voucher) . "\n";
            }
    
            // Send headers for file download
            header('Content-Type: text');
            header('Content-Disposition: attachment; filename="daftar_voucher.txt"');
    
            // Output txt data
            echo $txt_data;
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan voucher']);
        }
    }   
    
    if (isset($_POST['action']) && $_POST['action'] == 'update_tabel_voucher') {
        $ambilsemuadatavoucher = mysqli_query($conn, "SELECT * FROM vouchers");
        $i = 1;
        $html = '';
        while($data = mysqli_fetch_array($ambilsemuadatavoucher)){
            $code = $data['code'];
            $discount_amount = $data['discount_amount'];
            $is_used = $data['is_used'];
            $id = $data['id'];
            $created_at = $data['created_at'];
            $used_at = $data['used_at'];
    
            $status = ($is_used == 1) ? "Sudah digunakan" : "Belum digunakan";
    
            $html .= '<tr>';
            $html .= '<td>'.$i++.'</td>';
            $html .= '<td>'.$code.'</td>';
            $html .= '<td>'.$discount_amount.'</td>';
            $html .= '<td>'.$status.'</td>';
            $html .= '<td>'.$created_at.'</td>';
            $html .= '<td>'.$used_at ? $used_at : '-'.'</td>';
            $html .= '</tr>';
        }
        echo $html;
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
    $deskripsi = $_POST['deskripsi'];
    $price = $_POST['price'];
    $id = $_POST['id'];
    $content = $_POST['content'];

    $addtotable = mysqli_query($conn, "insert into products3 (name, deskripsi, price, id, content) values('$name', '$deskripsi', '$price', '$id', '$content')");

    if ($addtotable) {
        header("location:produk.php");
    } else {
        echo 'Gagal';
        header('location:produk.php');
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

//edit produk
if(isset($_POST['updatebarang'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $discount = $_POST['discount'];
    $price = $_POST['price'];
    $stok = $_POST['stok'];

    $updatep = mysqli_query($conn,"update products set name='$name', discount='$discount', price='$price', stok='$stok' where id ='$id'");
    if($updatep){
        header("location:produk.php");
    } else{
        echo 'Gagal';
        header('location:produk.php');
    }
}


//hapus produk
if (isset($_POST['hapusbarang'])) {
    $id = $_POST['id'];

    $hapusp = mysqli_query($conn,"delete from products where id='$id'");
    if($hapusp){
        header("location:produk.php");
    } else{
        echo 'Gagal';
        header('location:produk.php');
    }

}

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
            $hapusv = mysqli_query($conn, "DELETE FROM vouchers2 WHERE id='$id'");
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

//hapus voucher yang sudah digunakan
if (isset($_POST['hapus_voucher_digunakan'])) {
    $hapus_voucher = mysqli_query($conn, "DELETE FROM vouchers2 WHERE is_used = 1");
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