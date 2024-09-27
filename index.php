<?php
require 'function.php';
require 'cek.php';
?>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="description" content="" />
  <meta name="author" content="" />
  <title>User</title>
  <link href="css/style.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
  <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand" href="index.php" style="color: white;">Daclen</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
  </nav>
  <div id="layoutSidenav">
    <div id="layoutSidenav_nav">
      <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
          <div class="nav">
            <a class="nav-link" href="index.php">
              <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
              User
            </a>
            <a class="nav-link" href="produk.php">
              <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
              Produk
            </a>
            <a class="nav-link" href="transaksi.php">
              <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
              Transaksi
            </a>
            <a class="nav-link" href="voucher.php">
              <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
              Voucher
            </a>
            <a class="nav-link" href="logout.php">
              <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
              Logout
            </a>
          </div>
        </div>
      </nav>
    </div>
    <div id="layoutSidenav_content">
      <main>
        <div class="container-fluid">
          <h1 class="mt-4">User</h1>
          <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">User</li>
          </ol>
          <div class="card mb-4">
            <div class="card-header">
              <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal">
                Tambah User
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Nama User</th>
                      <th>Status</th>
                      <th>Umur</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $ambilsemuadatauser = mysqli_query($conn, "select * from user");
                    $i = 1;
                    while($data=mysqli_fetch_array($ambilsemuadatauser)){
                      $namauser = $data['namauser'];
                      $status = $data['status'];
                      $umur = $data['umur'];
                      $idu = $data['iduser'];
                    ?>
                    <tr>
                      <td><?=$i++;?></td>
                      <td><?=$namauser;?></td>
                      <td><?=$status;?></td>
                      <td><?=$umur;?></td>
                      <td>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?=$idu;?>">
                          Edit
                        </button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?=$idu;?>">
                          Delete
                        </button>
                      </td>
                    </tr>
                    <?php
                    };
                    ?>
                  </tbody>
                </table>
              </div>
            </div>