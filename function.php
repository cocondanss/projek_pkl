<?php
session_start();
//koneksi ke database
$conn =mysqli_connect("localhost","root","","dashboard");
//if($conn){
//    echo 'berhasil';
//}

//menambah user baru
    if (isset($_POST['TambahUser'])) {
        $namauser = $_POST['namauser'];
        $status = $_POST['status'];
        $umur = $_POST['umur'];

    $addtotable = mysqli_query($conn,"insert into user (namauser, status, umur) values('$namauser','$status','$umur')");

    if($addtotable){
        header("location:index.php");
    } else{
        echo 'Gagal';
        header('location:index.php');
    }
}


//menambah voucher
    if (isset($_POST['TambahVoucher'])) {
        $codevoucher = $_POST['codevoucher'];
        $diskon = $_POST['diskon'];
        $keterangan = $_POST['keterangan'];

    $addtotable = mysqli_query($conn,"insert into voucher (codevoucher, diskon, keterangan) values('$codevoucher','$diskon','$keterangan')");

    if($addtotable){
        header("location:voucher.php");
    } else{
        echo 'Gagal';
        header('location:voucher.php');
    }
    }

//menambah transaksi
if (isset($_POST['TambahTransaksi'])) {
    $namabarang = $_POST['namabarang'];
    $harga= $_POST['harga'];
    $tanggal = $_POST['tanggal'];
    $penerima = $_POST['penerima'];

$addtotable = mysqli_query($conn,"insert into transaksi (namabarang, harga, tanggal, penerima) values('$namabarang','$harga','$tanggal','$penerima')");

if($addtotable){
    header("location:transaksi.php");
} else{
    echo 'Gagal';
    header('location:transaksi.php');
}
}

//menambah produk
if (isset($_POST['TambahProduk'])) {
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];

$addtotable = mysqli_query($conn,"insert into produk (namabarang, deskripsi, harga) values('$namabarang','$deskripsi','$harga')");

if($addtotable){
    header("location:produk.php");
} else{
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
        header("location:index.php");
    } else{
        echo 'Gagal';
        header('location:index.php');
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

//edit produk
if(isset($_POST['updatebarang'])){
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];

    $updatep = mysqli_query($conn,"update produk set namabarang='$namabarang', deskripsi='$deskripsi', harga='$harga' where idbarang ='$idb'");
    if($updatep){
        header("location:produk.php");
    } else{
        echo 'Gagal';
        header('location:produk.php');
    }
}


//hapus produk
if (isset($_POST['hapusbarang'])) {
    $idb = $_POST['idb'];

    $hapusp = mysqli_query($conn,"delete from produk where idbarang='$idb'");
    if($hapusp){
        header("location:produk.php");
    } else{
        echo 'Gagal';
        header('location:produk.php');
    }

}


//edit transaksi
if(isset($_POST['updatetransaksi'])){
    $idt = $_POST['idt'];
    $namabarang = $_POST['namabarang'];
    $harga = $_POST['harga'];
    $tanggal = $_POST['tanggal'];
    $penerima = $_POST['penerima'];

    $updatet = mysqli_query($conn,"update transaksi set namabarang='$namabarang', harga='$harga', tanggal='$tanggal', penerima='$penerima' where idtransaksi ='$idt'");
    if($updatet){
        header("location:transaksi.php");
    } else{
        echo 'Gagal';
        header('location:transaksi.php');
    }
}


//hapus transaksi
if (isset($_POST['hapustransaksi'])) {
    $idt = $_POST['idt'];

    $hapust = mysqli_query($conn,"delete from transaksi where idtransaksi='$idt'");
    if($hapust){
        header("location:transaksi.php");
    } else{
        echo 'Gagal';
        header('location:transaksi.php');
    }

}


//edit voucher
if(isset($_POST['updatevoucher'])){
    $idv = $_POST['idv'];
    $codevoucher = $_POST['codevoucher'];
    $diskon = $_POST['diskon'];
    $keterangan = $_POST['keterangan'];

    $updatev = mysqli_query($conn,"update voucher set codevoucher='$codevoucher', diskon='$diskon', keterangan='$keterangan' where idvoucher ='$idv'");
    if($updatev){
        header("location:voucher.php");
    } else{
        echo 'Gagal';
        header('location:voucher.php');
    }
}


//hapus voucher
if (isset($_POST['hapusvoucher'])) {
    $idv = $_POST['idv'];

    $hapusv = mysqli_query($conn,"delete from voucher where idvoucher='$idv'");
    if($hapusv){
        header("location:voucher.php");
    } else{
        echo 'Gagal';
        header('location:voucher.php');
    }

}