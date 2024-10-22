<?php
require 'function.php';

//cek login, terdaftar atau tidak
if(isset($_POST['login'])){
     $email  = $_POST['email'];
     $password = $_POST['password'];

     //cocokan dengan database
     $cekdatabase = mysqli_query($conn, "SELECT * FROM login where email='$email' and password='$password'");
     //hitung jumlah data
     $hitung = mysqli_num_rows($cekdatabase);

     if($hitung>0){
        $_SESSION['log'] = 'true';
        header('location:index.php');
     } else {
        header('location:login.php');
     };
};

    if(!isset($_SESSION['log'])){

    } else {
        header('location:index.php');
    }

    if(isset($_POST['kembali'])){    
        header('location:listproduct.php');
        exit;
    }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Login</title>
        <link href="css/style.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-dark">
            <div>
                <main>
                    <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-bold my-4">Login As Admin</h3></div>
                                    <div class="card-body">
                                        <form method="post">
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputEmailAddress">email</label>
                                                <input class="form-control py-4" name="email" id="inputEmailAddress" type="Id" placeholder="Masukan email admin" />
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputPassword">Password</label>
                                                <input class="form-control py-4" name="password" id="inputPassword" type="password" placeholder="Masukan password" />
                                            </div>
                                            <div class="form-group">
                                            </div>
                                            <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <button class="btn btn-info" name="login"> Masuk</a>
                                                <button class="btn btn-secondary" name="kembali"> Kembali</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>      
                    <main>
                </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
