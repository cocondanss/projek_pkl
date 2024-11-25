<?php
// Memanggil file function.php yang berisi konfigurasi dan fungsi-fungsi pendukung
require 'function.php';

// Proses autentikasi login admin
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Mengambil data email dan password admin dari tabel settings
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_email', 'admin_password')");
    $stmt->execute();
    $result = $stmt->get_result();
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    // Validasi email dan password
    if($email === $settings['admin_email'] && $password === $settings['admin_password']){
        // Jika login berhasil, set session dan redirect ke halaman index
        $_SESSION['log'] = 'true';
        header('location:index.php');
        exit;
    } else {
        // Jika login gagal, tampilkan pesan error
        $error = "Email atau password salah";
        echo "<script>alert('Email atau password salah!');</script>";
    }
}

// Cek status login
if(!isset($_SESSION['log'])){
    // Jika belum login, tetap di halaman login
} else {
    // Jika sudah login, redirect ke halaman index
    header('location:index.php');
}

// Handler untuk tombol kembali
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
    <!-- <link href="css/style.css" rel="stylesheet" /> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    <style>
        body {
            overflow-x: hidden; /* Hides horizontal scrollbar */
            overflow-y: hidden; /* Hides vertical scrollbar */
            animation: gradient 10s ease infinite; /* Add animation */
            background: linear-gradient(45deg, #ff6b6b, #f7b733, #6a82fb, #fc5c7d); /* Initial gradient */
            background-size: 400% 400%; /* For smooth transition */
            display: flex; /* Use flexbox */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full viewport height */
            font-family: 'Poppins', sans-serif;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card {
            width: 500px; /* Perbesar lebar card */
            height: 400px; /*pertinggi card
            border: none; /* Remove default border */
            border-radius: 15px; /* Rounded corners */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2); /* Tambah bayangan yang lebih dalam */
            transition: transform 0.3s; /* Smooth transition for hover effect */
        }

        .card:hover {
            transform: scale(1.05); /* Scale up on hover */
        }

        .form-control {
            border-radius: 10px; /* Rounded corners for input fields */
            border: 1px solid #ced4da; /* Border color */
            transition: border-color 0.3s; /* Smooth transition for border color */
        }

        .form-control:focus {
            border-color: #007bff; /* Change border color on focus */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add shadow on focus */
        }

        .btn-info {
            width: 100%; /* Tombol mengisi lebar penuh */
            border-radius: 10px; /* Rounded corners for buttons */
            transition: background-color 0.3s, transform 0.3s; /* Smooth transition */
        }

        .btn-info:hover {
            background-color: #0056b3; /* Darker shade on hover */
            transform: translateY(-2px); /* Efek angkat saat hover */
        }

        .btn-secondary {
            width: 100%; /* Tombol mengisi lebar penuh */
            border-radius: 10px; /* Rounded corners for buttons */
            transition: background-color 0.3s, transform 0.3s; /* Smooth transition */
        }

        .btn-secondary:hover {
            background-color: #5a6268; /* Darker shade on hover */
            transform: translateY(-2px); /* Efek angkat saat hover */
        }
    </style>
</head>
<body class="bg-dark">
    <div>
        <main>
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <!-- Card untuk form login -->
                    <div class="card shadow-lg border-0 rounded-lg mt-5">
                        <div class="card-header">
                            <h3 class="text-center font-weight-bold my-4">Login As Admin</h3>
                        </div>
                        <div class="card-body">
                            <!-- Form login -->
                            <form method="post">
                                <!-- Input email -->
                                <div class="form-group">
                                    <label class="small mb-1" for="inputEmailAddress">Email</label>
                                    <input class="form-control py-4" 
                                           name="email" 
                                           id="inputEmailAddress" 
                                           type="email" 
                                           placeholder="Masukan email admin"/>
                                </div>
                                <!-- Input password -->
                                <div class="form-group">
                                    <label class="small mb-1" for="inputPassword">Password</label>
                                    <input class="form-control py-4" 
                                           name="password" 
                                           id="inputPassword" 
                                           type="password" 
                                           placeholder="Masukan password"/>
                                </div>
                                <!-- Tombol aksi -->
                                <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                    <button class="btn btn-info btn-sm" name="login" style="position: absolute; bottom: 10px; left: 10px;">Masuk</button>
                                    <button class="btn btn-secondary btn-sm" name="kembali" style="position: absolute; bottom: 10px; right: 10px;">Kembali</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>