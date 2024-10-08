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
        $code = $_POST['code'];
        $discount_amount = $_POST['discount_amount'];
        $keterangan = $_POST['is_used'];

    $addtotable = mysqli_query($conn,"insert into vouchers (code, discount_amount, is_used) values('$code','$discount_amount','$keterangan')");

    if($addtotable){
        header("location:voucher.php");
    } else{
        echo 'Gagal';
        header('location:voucher.php');
    }
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
        header("location:index.php");
    } else{
        echo 'Gagal';
        header('location:index.php');
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


//edit voucher
if(isset($_POST['updatevoucher'])){
    $id = $_POST['id'];
    $code = $_POST['code'];
    $discount_amount = $_POST['discount_amount'];
    $keterangan = $_POST['is_used'];

    $updatev = mysqli_query($conn,"update vouchers set code='$code', discount_amount='$discount_amount', is_used='$keterangan' where id ='$id'");
    if($updatev){
        header("location:voucher.php");
    } else{
        echo 'Gagal';
        header('location:voucher.php');
    }
}


//hapus voucher
if (isset($_POST['hapusvoucher'])) {
    $id = $_POST['id'];

    $hapusv = mysqli_query($conn,"delete from vouchers where id='$id'");
    if($hapusv){
        header("location:voucher.php");
    } else{
        echo 'Gagal';
        header('location:voucher.php');
    }

}


// Fetch products from database
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);