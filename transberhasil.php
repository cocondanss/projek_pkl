<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Berhasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .message-box {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 500px;
        }
        .message-box h1 {
            color: #4caf50;
            margin: 0 0 10px;
        }
        .message-box p {
            font-size: 16px;
            margin: 0;
        }
        .back-button {
            margin-top: 20px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #4caf50;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="message-box">
            <h1>Transfer Berhasil</h1>
            <p>Transfer Anda telah berhasil dilakukan. Terima kasih telah menggunakan layanan kami.</p>
            <a href="index.html" class="back-button">Kembali ke Beranda</a>
        </div>
    </div>
    <?php
    session_start();

    if (isset($_SESSION['order_id'])) {
        $order_id = $_SESSION['order_id'];

        // Tampilkan pesan transaksi sukses
        echo "Transaksi dengan order ID $order_id telah berhasil!";
    } else {
        echo "Tidak ada order ID yang tersedia.";
    }

    // Hapus order ID dari session
    unset($_SESSION['order_id']);
    ?>
</body>

</html>
