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
    <link href="css/styleL.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            overflow-x: hidden; /* Hides horizontal scrollbar */
            overflow-y: hidden; /* Hides vertical scrollbar */
            animation: gradient 10s ease infinite; /* Add animation */
            background: linear-gradient(45deg, #1a1a1a, #333333, #4d4d4d, #1a1a1a); /* Dark theme gradient with right transition */
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

        .shadow-lg {
            background: whitesmoke;
            padding: 50px;
        }

        .text-center {
            font-size: x-large;
            position: relative;
            bottom: 33px;
            margin-bottom:30px;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
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
                                <div class="form-group" style="position:relative; bottom: 15px;">
                                    <label class="small mb-1" for="inputEmailAddress">Email</label>
                                    <input class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-300" 
                                           name="email" 
                                           id="inputEmailAddress" 
                                           type="email" 
                                           placeholder="Masukan email admin"/
                                           style="margin-right: 100px;">
                                </div>
                                <!-- Input password -->
                                <div class="form-group" style="margin-top: 13px;">
                                    <label class="small mb-1" for="inputPassword">Password</label>
                                    <input class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-300" 
                                           name="password" 
                                           id="inputPassword" 
                                           type="password" 
                                           placeholder="Masukan password"/>
                                </div>
                                <!-- Tombol aksi -->
                                <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                    <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300" name="login" style="position:relative; top:15px; right:15px; margin-top:30px;">Masuk</button>
                                    <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300" name="kembali" style="position:relative; top:15px; left:200px;">Kembali</button>
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