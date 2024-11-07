<?php
/**
 * List Product Page
 * File: listproduct.php
 * Fungsi: Menampilkan daftar produk dan mengelola sistem voucher
 */

require 'function.php';

/**
 * Fungsi untuk menerapkan voucher pada harga produk
 * @param string $voucherCode - Kode voucher yang diinput
 * @param float $price - Harga asli produk
 * @return float - Harga setelah penerapan voucher
 */
function applyVoucher($voucherCode, $price) {
    global $conn;
    
    $debug_info = "Voucher Code: $voucherCode, Original Price: $price\n";

    // Persiapkan query untuk mencari voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $debug_info .= "Voucher found: " . print_r($row, true) . "\n";
        $discountAmount = $row['discount_amount'];
        $debug_info .= "Discount Amount: $discountAmount\n";

        // Cek tipe diskon (persentase atau nominal)
        if ($discountAmount <= 100) {
            // Diskon persentase
            $discountedPrice = $price - ($price * ($discountAmount / 100));
        } else {
            // Diskon nominal langsung
            $discountedPrice = $price - $discountAmount;
        }
        
        $debug_info .= "Calculated Discounted Price: $discountedPrice\n";
        // Pastikan harga tidak negatif
        $finalPrice = max($discountedPrice, 0);
        
        return $finalPrice;
    }

    $debug_info .= "No voucher found\n";
    return $price;
}

// Inisialisasi variabel untuk sistem voucher
$voucherMessages = [];
$voucherCode = '';

// Proses pengecekan voucher saat ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    $voucherCode = trim($_POST['voucher_code']);
    
    // Validasi voucher
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Cek apakah voucher sudah digunakan (untuk voucher sekali pakai)
        if ($row['one_time_use'] == 1 && $row['used_at'] !== null) {
            $voucherMessages[] = "<p class='voucher-message error'>Voucher hanya dapat digunakan sekali</p>";
        } else {
            // Update status penggunaan voucher
            date_default_timezone_set('Asia/Jakarta');
            $currentDateTime = date('Y-m-d H:i:s');
            $updateStmt = $conn->prepare("UPDATE vouchers2 SET used_at = ? WHERE code = ?");
            $updateStmt->bind_param("ss", $currentDateTime, $voucherCode);
            $updateStmt->execute();
            
            $voucherMessages[] = "<p class='voucher-message success'>Voucher berhasil digunakan.</p>";
        }
    } else {
        $voucherMessages[] = "<p class='voucher-message error'>Voucher tidak valid.</p>";
    }
}

// Ambil data produk yang visible
$produk = mysqli_query($conn, "SELECT * FROM products WHERE visible = 1");
if (!$produk) {
    die("Query gagal: " . mysqli_error($conn));
}

// Mulai output buffering untuk request AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    ob_start();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .container-index {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            text-align: left;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* height: 100vh; */
        }

        .header-index {
            padding-top: 20px;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .product {
            background-color: #2b2d42;
            color: white;
            border-radius: 30px;
            padding: 25px;
            margin: 20px;
            width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .product h2 {
            margin-top: 0;
            font-size: 24px;
        }

        .product p {
            margin: 10px 0;
        }

        .product button {
            margin-left: 73%;
            background-color: #d3d3d3;
            color: #2b2d42;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            align-self: flex-end;
            min-width: 80px; /* Memberikan lebar minimum */
            white-space: nowrap;
        }

        .product button:hover {
            background-color: #b0b0b0;
        }

        #modal-price {
            font-size: 24px;
            font-weight: bold;
            color: #2b2d42;
            margin-bottom: 20px;
            text-align: center;
        }
        .product .price-changed {
            animation: highlight 4s ease-in-out;
        }

        .product .original-price {
            text-decoration: line-through;
            color: #a0a0a0;
        }

        .product .discounted-price {
            color: white;
        }
        .voucher-form {
            margin:20px auto;
            width: 300px;
        }

        .voucher-form input[type="text"] {
            width: 100%;
            margin-right: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }

        .voucher-form button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #282A51;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            
        }

        .voucher-form button[type="submit"]:hover {
            background-color: #2B3044;
        }

        .modal-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .calculator-container {
            text-align: center;
        }

        .calculator {
            width: 250px;
            padding: 20px;
            /* border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); */
            background-color: #ffffff;
            margin-bottom: 20px;
            margin: 0 auto;
        }

        .display {
            width: 100%;
            height: 50px;
            background-color: #6c757d;
            color: #ffffff;
            text-align: center;
            line-height: 50px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .btn {
            width: 60px;
            height: 60px;
            margin: 5px;
            font-size: 24px;
            border-radius: 10px;
        }

        .btn-number {
            background-color: #6c757d;
            color: #ffffff;
        }

        .btn-backspace {
            background-color: #dc3545;
            color: #ffffff;
        }

        .btn-enter {
            background-color: #28a745;
            color: #ffffff;
        }

        .modal-content {
            background-color: rgba(0, 0, 0, 0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin-bottom: 0;
        }

        .modal-footer .btn {
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
        }

        .back-button {
            width: 70%;
            max-width: 220px;
        }

        .qr-modal .modal-content {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: none;
            overflow: hidden;
            max-width: 550px;
            position: absolute;
            left: 150px;
        }

        .qr-modal .modal-header {
            background-color: #2b2d42;
            color: white;
            border-bottom: none;
            padding: 20px 30px;
        }

        .qr-modal .modal-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .qr-modal .btn-close {
            background-color: transparent;
            border: 2px solid white;
            border-radius: 50%;
            padding: 8px;
            opacity: 1;
        }

        .qr-modal .btn-close:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .qr-modal .modal-body {
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
        }

        .qr-modal .qr-code-container {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            display: inline-block;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .qr-modal .qr-code-image {
            max-width: 250px;
            height: auto;
            border-radius: 10px;
            display: block;
            margin: 0 auto;
        }

        .qr-modal #countdown {
            font-size: 20px;
            font-weight: bold;
            color: #2b2d42;
            margin: 15px 0;
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .qr-modal .status-message {
            margin: 15px 0;
            min-height: 30px;
        }

        .qr-modal .status-message .alert {
            border-radius: 8px;
            padding: 10px 15px;
            margin: 0;
            font-weight: 500;
        }

        .qr-modal .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .qr-modal .btn {
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
            min-width: 120px;
        }

        .qr-modal .btn-cancel {
            background-color: #2b2d42;
            color: white;
            border: none;
        }

        .qr-modal .btn-cancel:hover {
            background-color: #e9ecef;
            color: #2b2d42;
            transform: translateY(-2px);
            border: 2px solid #2b2d42;
        }

        .qr-modal #btn-check {
            background-color: #e9ecef;
            color: #2b2d42;
            border: 2px solid #2b2d42;
        }

        .qr-modal #btn-check:hover {
            background-color: #2b2d42;
            color: white;
            transform: translateY(-2px);
        }

        .qr-modal .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Loading spinner styles */
        .qr-modal .spinner-border {
            width: 1.2rem;
            height: 1.2rem;
            margin-right: 8px;
        }

        /* Modal backdrop style */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.7);
        }

        /* Responsive styles */
        @media (max-width: 576px) {
            .qr-modal .modal-body {
                padding: 20px;
            }

            .qr-modal .qr-code-image {
                max-width: 200px;
            }

            .qr-modal .button-container {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .qr-modal .btn {
                padding: 8px 20px;
                min-width: 140px;
            }
        }

        /* Animation styles */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .qr-modal.show {
            animation: fadeIn 0.3s ease-out;
        }

        /* Alert styles */
        .qr-modal .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .qr-modal .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        .qr-modal .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        #btn-check {
            background-color: #e9ecef;
            font-size: 100%;
            color: #2b3242;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 120px;
            position: relative;
        }
        #voucher-form {
            width: 400px;
            padding: 20px;
            margin-left: 50%;
            margin-bottom: 15px;
            /* border: 1px solid #ccc;*/
            border-radius: 5px;
            position: relative;
            left: 3px;
        }

        #product-list {
            width: 1000px;
            display: ruby;
            position: relative;
            top: 20px;
            left: 43px;
        }
        #product-container {
            max-width: 1200px;
            /* margin: 0 auto; */
            padding: 20px;
            flex: 1;
            overflow-y: auto;
            margin-top: 10px;
            
        }
        h1.product-list-title {
            margin-bottom: 10px;
            font-size: 24px;
            color: #333;
            /* margin: 0 0 10px 0; */
            padding: 10px 0;
            border-bottom: 2px solid #eee;
        }
        .price-container {
            min-height: 50px; 
            transition: all 0.3s ease;

        }
        #product-container {
            margin-top: 10px;
        }
        #voucher-message-container {
            transition: opacity 0.5s ease-in-out;
        }
        .voucher-message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .voucher-message.error {
            margin-left: 36%;
            width: 100%;
            background-color: #ffecec;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .voucher-message.success {
            margin-left: 28%;
            width: 100%;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        /* Modifikasi style yang sudah ada */
        .fa-keyboard {
            color: #6c757d;
            font-size: 20px;
        }

        .fa-keyboard:hover {
            color: #495057;
        }
        .virtual-keyboard {
            padding: 20px;
            background-color: #ffffff; /* Menambahkan background putih */
            width: 100%;
        }

        .modal-content {
            background-color: #ffffff !important; /* Memastikan background modal putih */
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .modal-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-body {
            background-color: #ffffff;
            border-radius: 10px;
        }

        .keyboard-row {
            display: flex;
            justify-content: center;
            margin-bottom: 10px; /* Menambah jarak antar baris */
        }

        .key {
            width: 70px; /* Memperbesar ukuran tombol */
            height: 70px; /* Memperbesar ukuran tombol */
            margin: 4px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #ffffff;
            font-size: 25px; /* Memperbesar ukuran font */
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            color: #333;
        }

        .key:hover {
            background-color: #a0a0a0; /* Warna hover yang lebih subtle */
        }

        .key.backspace {
            width: 80px;
            background-color: #ffffff;
        }

        .key.backspace:hover {
            background-color: #a0a0a0;
        }

        .key.space {
            width: 200px; /* Lebih lebar dari tombol lain */
        }

        .modal-title {
            width: 100%;
            display: flex;
            justify-content: flex-end; /* Memindahkan tombol close ke kanan */
            padding: 10px 20px;
        }

        /* Memastikan tombol close memiliki warna yang sesuai */
        .btn-close {
            font-size: 1.5rem; /* Memperbesar tombol close */
            padding: 10px;
        }

        .modal-dialog {
            max-width: 800px; /* Memperbesar ukuran modal */
            margin: 1.75rem auto;
        }

        .key.caps-lock {
            width: 80px; /* Lebih lebar dari tombol biasa */
            background-color: #ffffff;
            position: relative;
        }

        .key.caps-lock.active {
            background-color: #4CAF50; /* Warna hijau saat aktif */
            color: white;
        }

        /* Tambahkan indikator LED untuk Caps Lock */
        .key.caps-lock::after {
            content: '';
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #ccc;
        }

        .key.caps-lock.active::after {
            background-color: #4CAF50; /* LED hijau saat aktif */
        }

        .keyboard-display {
            background-color: #2b2d42;
            color: #ffffff;
            border: 1px solid;
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            margin-right: 19%;
            min-height: 73px;
            width: 50%;
            font-size: 20px;
            text-align: center;
            position: relative;
        }

        .keyboard-display:empty::before {
            content: attr(placeholder);
            color: rgba(255, 255, 255, 0.5);
            position: absolute;
            left: 100px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .keyboard-display.active:empty::before {
            content: '';
        }

    </style>
</head>
<body>
    <div class="container-index" style="max-width: 100%;">
        <div class="header-index">
            <div class="container-button">
                <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#keypadModal"
                    style="position: absolute; right: 30px; top: 30px; background: none; border: none;">
                    <i class="fas fa-lock" style="font-size: 20px; color: rgba(0, 0, 0, 0.2);"></i>
                </button>
            </div>
            <div class="product-container">
                <div class="row">
                    <div class="product-list" style="background: none;" id="product-list">
                        <?php foreach ($produk as $item): 
                            $originalPrice = $item['price'];
                            $discountedPrice = applyVoucher($voucherCode, $originalPrice);
                            ?>
                            <div class="product" data-product-id="<?php echo $item['id']; ?>" style="">
                                <div class="card-body"> 

                                    <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                                    <div class="price-container">
                                        <?php if ($discountedPrice < $originalPrice): ?>
                                            <p class="original-price">Rp <span><?php echo number_format($originalPrice, 0, ',', '.'); ?></span></p>
                                            <p class="discounted-price">Rp <span><?php echo number_format($discountedPrice, 0, ',', '.'); ?></span></p>
                                            <?php else: ?>
                                                <p>Rp <span><?php echo number_format($originalPrice, 0, ',', '.'); ?></span></p>
                                                <?php endif; ?>
                                            </div>
                                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                                            <button onclick="showPaymentModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', <?php echo $discountedPrice; ?>)">Buy</button>
                                        </div>
                                    </div>
                                        <?php endforeach; ?>
                                        <div class="voucher-form">
                                            <div id="voucher-message-container">
                                                <?php
                                                // Tampilkan semua pesan voucher
                                                foreach ($voucherMessages as $message) {
                                                echo $message;
                                                }
                                                ?>
                                            </div>
                                <form id="voucher-form" method="POST">
                                    <input type="text" name="voucher_code" id="voucher-input" placeholder="Masukkan kode voucher" onclick="showVirtualKeyboard()">
                                    <button type="submit">Terapkan Voucher</button>
                                </form>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="virtualKeyboardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="width: 120%   ; right: 50px;">
                <div class="modal-title">
                    <div id="keyboard-display" class="keyboard-display" placeholder="Masukkan Kode Voucher"></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="margin-left: 50; position: relative "></button>
                </div>
                <div class="modal-body">
                    <div class="virtual-keyboard">
                        <div class="keyboard-row">
                            <button type="button" class="key">1</button>
                            <button type="button" class="key">2</button>
                            <button type="button" class="key">3</button>
                            <button type="button" class="key">4</button>
                            <button type="button" class="key">5</button>
                            <button type="button" class="key">6</button>
                            <button type="button" class="key">7</button>
                            <button type="button" class="key">8</button>
                            <button type="button" class="key">9</button>
                            <button type="button" class="key">0</button>
                            <button type="button" class="key">_</button>
                        </div>
                        <div class="keyboard-row">
                            <button type="button" class="key caps-lock">Caps</button>
                            <button type="button" class="key">q</button>
                            <button type="button" class="key">w</button>
                            <button type="button" class="key">e</button>
                            <button type="button" class="key">r</button>
                            <button type="button" class="key">t</button>
                            <button type="button" class="key">y</button>
                            <button type="button" class="key">u</button>
                            <button type="button" class="key">i</button>
                            <button type="button" class="key">o</button>
                            <button type="button" class="key">p</button>
                        </div>
                        <div class="keyboard-row">
                            <button type="button" class="key">a</button>
                            <button type="button" class="key">s</button>
                            <button type="button" class="key">d</button>
                            <button type="button" class="key">f</button>
                            <button type="button" class="key">g</button>
                            <button type="button" class="key">h</button>
                            <button type="button" class="key">j</button>
                            <button type="button" class="key">k</button>
                            <button type="button" class="key">l</button>
                        </div>
                        <div class="keyboard-row">
                            <button type="button" class="key">z</button>
                            <button type="button" class="key">x</button>
                            <button type="button" class="key">c</button>
                            <button type="button" class="key">v</button>
                            <button type="button" class="key">b</button>
                            <button type="button" class="key">n</button>
                            <button type="button" class="key">m</button>
                            <button type="button" class="key backspace"><i class="fas fa-backspace"></i></button>
                        </div>
                        <div class="keyboard-row">
                            <button type="button" class="key space">Space</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="modal fade" id="keypadModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="display: flex; width: auto; margin: 0 auto;">
                    <div class="modal-body">
                        <div class="calculator">
                            <div class="display" id="display"></div>
                            <div class="d-flex flex-wrap justify-content-center">
                                <button class="btn btn-number" onclick="appendNumber('1')">1</button>
                                <button class="btn btn-number" onclick="appendNumber('2')">2</button>
                                <button class="btn btn-number" onclick="appendNumber('3')">3</button>
                                <button class="btn btn-number" onclick="appendNumber('4')">4</button>
                                <button class="btn btn-number" onclick="appendNumber('5')">5</button>
                                <button class="btn btn-number" onclick="appendNumber('6')">6</button>
                                <button class="btn btn-number" onclick="appendNumber('7')">7</button>
                                <button class="btn btn-number" onclick="appendNumber('8')">8</button>
                                <button class="btn btn-number" onclick="appendNumber('9')">9</button>
                                <button class="btn btn-backspace" onclick="backspace()"><i class="fas fa-backspace"></i></button>
                                <button class="btn btn-number" onclick="appendNumber('0')">0</button>
                                <button class="btn btn-enter" onclick="enter()"><i class="fas fa-check"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            let pinCode = '';
            let display = document.getElementById('display');

            document.addEventListener('DOMContentLoaded', function() {
                let isCapsLock = false;
                setupVirtualKeyboard();

                function setupVirtualKeyboard() {
                    const voucherInput = document.getElementById('voucher-input');
                    const keyboardDisplay = document.getElementById('keyboard-display');
                    const keys = document.querySelectorAll('.key');
                    const capsLockKey = document.querySelector('.caps-lock');

                    keyboardDisplay.addEventListener('click', function() {
                        // Hapus placeholder ketika display di-klik
                        if (!this.textContent) {
                            this.classList.add('active');
                        }
                    });
                    
                    // Event listener untuk tombol Caps Lock
                    if (capsLockKey) {
                        capsLockKey.addEventListener('click', function(e) {
                            e.preventDefault();
                            isCapsLock = !isCapsLock;
                            this.classList.toggle('active');
                            
                            // Update tampilan tombol huruf
                            updateKeyDisplay();
                        });
                    }
                    
                    keys.forEach(key => {
                        if (!key.classList.contains('caps-lock')) {
                            key.addEventListener('click', handleKeyClick);
                        }
                    });
                }

                function updateKeyDisplay() {
                    const letterKeys = document.querySelectorAll('.key:not(.caps-lock):not(.backspace):not(.space)');
                    letterKeys.forEach(key => {
                        if (key.textContent.length === 1) { // Hanya untuk tombol huruf tunggal
                            key.textContent = isCapsLock ? key.textContent.toUpperCase() : key.textContent.toLowerCase();
                        }
                    });
                }

                function handleKeyClick(event) {
                    event.preventDefault();
                    const voucherInput = document.getElementById('voucher-input');
                    const keyboardDisplay = document.getElementById('keyboard-display');
                    
                    if (this.classList.contains('backspace')) {
                        // Hapus karakter terakhir
                        voucherInput.value = voucherInput.value.slice(0, -1);
                        keyboardDisplay.textContent = voucherInput.value;
                        if (!voucherInput.value) {
                            keyboardDisplay.classList.remove('active');
                        }
                    } else if (this.classList.contains('space')) {
                        // Tambah spasi
                        voucherInput.value += ' ';
                        keyboardDisplay.textContent = voucherInput.value;
                    } else {
                        // Tambah karakter sesuai dengan status Caps Lock
                        let char = this.textContent;
                        if (!isCapsLock && char.length === 1) {
                            char = char.toLowerCase();
                        }
                        voucherInput.value += char;
                        keyboardDisplay.textContent = voucherInput.value;
                    }
                    
                    // Fokus kembali ke input setelah setiap klik
                    voucherInput.focus();
                }

                // Reset Caps Lock saat modal ditutup
                document.getElementById('virtualKeyboardModal').addEventListener('hidden.bs.modal', function () {
                    isCapsLock = false;
                    const capsLockKey = document.querySelector('.caps-lock');
                    const keyboardDisplay = document.getElementById('keyboard-display')
                    if (capsLockKey) {
                        capsLockKey.classList.remove('active');
                    }
                    updateKeyDisplay();
                    document.getElementById('voucher-input').focus();
                    keyboardDisplay.textContent = '';
                    keyboardDisplay.classList.remove('active');
                });
            });

            // Modifikasi fungsi showVirtualKeyboard
            function showVirtualKeyboard() {
                const modal = new bootstrap.Modal(document.getElementById('virtualKeyboardModal'));
                const voucherInput = document.getElementById('voucher-input');
                const keyboardDisplay = document.getElementById('keyboard-display');
                
                // Set nilai awal display dari input voucher
                keyboardDisplay.textContent = voucherInput.value;
                
                // Reset Caps Lock state
                const capsLockKey = document.querySelector('.caps-lock');
                if (capsLockKey) {
                    capsLockKey.classList.remove('active');
                }
                
                modal.show();
            }

            // Panggil fungsi saat DOM selesai dimuat
            document.addEventListener('DOMContentLoaded', showAndHideMessage);

            // Fungsi untuk menangani submit form voucher
            $(document).ready(function() {
                $('#voucher-form').on('submit', function(e) {
                    e.preventDefault();
                    var formData = $(this).serialize();

                    $.ajax({
                        url: 'listproduct.php',
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            var $response = $(response);
                            $('#product-list').html($response.find('#product-list').html());
                            
                            // Tampilkan pesan
                            var message = $response.find('#voucher-message-container').html();
                            $('#voucher-message-container').html(message).show();
                            
                            // Sembunyikan pesan setelah beberapa detik
                            setTimeout(function() {
                                $('#voucher-message-container').fadeOut(500, function() {
                                    $(this).html('');
                                });
                            }, 3000); // Pesan akan menghilang setelah 3 detik (3000 ms)
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            alert('Terjadi kesalahan saat memproses voucher. Silakan coba lagi.');
                        }
                    });
                });
            });

            function showPaymentModal(id, name, price) {
                if (id && name && price) {
                    document.getElementById('modal-product-id').value = id;
                    document.getElementById('modal-product-name').value = name;
                    document.getElementById('modal-product-price').value = price;
                    document.getElementById('modal-price').innerText = 'Rp ' + price;
                    $('#paymentModal').modal('show');
                } else {
                    console.error('Parameter tidak valid');
                }
            }


            function appendNumber(number) {
                if (pinCode.length < 4) {
                    pinCode += number;
                    display.textContent = '*'.repeat(pinCode.length);
                }
            }

            function backspace() {
                pinCode = pinCode.slice(0, -1);
                display.textContent = '*'.repeat(pinCode.length);
            }

            function enter() {
                if (pinCode.length === 4) {
                    $.ajax({
                        url: 'keypad.php',
                        method: 'POST',
                        data: { pin: pinCode },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                window.location.href = 'login.php';
                            } else {
                                $('#keypadModal').modal('hide');
                                $('#errorModal').modal('show');
                                pinCode = '';
                                display.textContent = '';
                            }
                        },
                        error: function () {
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            }

            // Add event listeners for keyboard input when the modal is open
            $('#keypadModal').on('shown.bs.modal', function () {
                $(document).on('keydown.keypad', function (event) {
                    if (event.key >= '0' && event.key <= '9' && pinCode.length < 4) {
                        appendNumber(event.key);
                    } else if (event.key === 'Backspace') {
                        backspace();
                    } else if (event.key === 'Enter') {
                        enter();
                    }
                });
            }).on('hidden.bs.modal', function () {
                $(document).off('keydown.keypad');
                pinCode = '';
                display.textContent = '';
            });
            
            function showPaymentModal(id, name, price, discount) {
                createTransaction(id, name, price, discount).then(response => {
                    if (response.success) {
                        // Hapus modal lama jika ada
                        const existingModal = document.getElementById('qrCodeModal');
                        if (existingModal) {
                            existingModal.remove();
                        }
                        // Buat elemen modal baru
                        const modalHTML = `
                            <div class="modal fade qr-modal" id="qrCodeModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Scan QR Code untuk Pembayaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="qr-code-container">
                                                <img id="qrCodeImage" src="" alt="QR Code" class="qr-code-image">
                                            </div>
                                            <div id="countdown"></div>
                                            <div class="status-message"></div>
                                            <div class="button-container">
                                                <button type="button" class="btn btn-cancel" id="btn-cancel" onclick="cancelTransaction()">
                                                    Batal
                                                </button>
                                                <button type="button" class="btn" id="btn-check" onclick="checkPaymentStatus()">
                                                    Cek
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        // Tambahkan modal ke body
                        document.body.insertAdjacentHTML('beforeend', modalHTML);
                        
                        // Dapatkan referensi ke modal yang baru dibuat
                        const qrCodeModal = document.getElementById('qrCodeModal');
                        const qrCodeImage = qrCodeModal.querySelector('#qrCodeImage');
                        
                        // Set QR code image
                        qrCodeImage.src = response.qr_code_url;
                        
                        // Set transaction ID
                        qrCodeModal.setAttribute('data-transaction-id', response.order_id);

                        // Start the countdown timer
                        startCountdown(30 * 60); // 30 minutes in seconds

                        // Tampilkan modal
                        const modalInstance = new bootstrap.Modal(qrCodeModal);
                        modalInstance.show();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }).catch(error => {
                    console.error('Error in createTransaction:', error);
                    alert('Terjadi kesalahan saat membuat transaksi.');
                });
            }


            // Add countdown timer function
            function startCountdown(duration) {
                let timer = duration;
                const countdownElement = document.getElementById('countdown');
                let countdown = setInterval(function() {
                    const minutes = parseInt(timer / 60, 10);
                    const seconds = parseInt(timer % 60, 10);

                    countdownElement.textContent = minutes.toString().padStart(2, '0') + ':' + 
                                                seconds.toString().padStart(2, '0');

                    if (--timer < 0) {
                        clearInterval(countdown);
                        const modal = document.getElementById('qrCodeModal');
                        const statusMessage = modal.querySelector('.status-message');
                        statusMessage.innerHTML = '<div class="alert alert-danger" role="alert">QR Code telah kadaluarsa. Silakan lakukan pemesanan ulang.</div>';
                        
                        setTimeout(() => {
                            const qrCodeModal = bootstrap.Modal.getInstance(modal);
                            qrCodeModal.hide();
                        }, 3000);
                    }
                }, 1000);

                // Store the interval ID in the modal element
                const modal = document.getElementById('qrCodeModal');
                modal.setAttribute('data-countdown-id', countdown);

                // Clear the interval when the modal is closed
                modal.addEventListener('hidden.bs.modal', function() {
                    clearInterval(countdown);
                });
            }

            function createTransaction(id, name, price, discount) {
                return fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'create_transaction',
                        product_id: id,
                        product_name: name,
                        product_price: price,
                        discount: discount
                    })
                })
                    .then(response => response.json())
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memproses permintaan.');
                    });
            }

            // Tambahkan fungsi untuk membatalkan transaksi
            function cancelTransaction() {
                const modal = document.getElementById('qrCodeModal');
                const statusMessage = modal.querySelector('.status-message');
                const cancelButton = modal.querySelector('#btn-cancel');
                const checkButton = modal.querySelector('#btn-check');
                
                cancelButton.disabled = true;
                cancelButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Membatalkan...';
                checkButton.disabled = true;
                
                const transactionId = getCurrentTransactionId();
                
                fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'cancel_transaction',
                        transaction_id: transactionId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusMessage.innerHTML = '<div class="alert alert-warning" role="alert">Transaksi dibatalkan</div>';
                        setTimeout(() => {
                            window.location.href = 'transbatal.php';
                        }, 1500);
                    } else {
                        cancelButton.disabled = false;
                        cancelButton.innerHTML = 'Batal';
                        checkButton.disabled = false;
                        statusMessage.innerHTML = '<div class="alert alert-danger" role="alert">Gagal membatalkan transaksi: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    cancelButton.disabled = false;
                    cancelButton.innerHTML = 'Batal';
                    checkButton.disabled = false;
                    statusMessage.innerHTML = '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat membatalkan transaksi.</div>';
                    console.error('Error:', error);
                });
            }

            // Update modal HTML untuk menambahkan tombol batal
            const modalHTML = `
                <div class="modal fade qr-modal" id="qrCodeModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Scan QR Code untuk Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="qr-code-container">
                                    <img id="qrCodeImage" src="" alt="QR Code" class="qr-code-image">
                                </div>
                                <div id="countdown"></div>
                                <div class="status-message"></div>
                                <div class="button-container">
                                    <button type="button" class="btn btn-cancel" id="btn-cancel" onclick="cancelTransaction()">
                                        Batal
                                    </button>
                                    <button type="button" class="btn" id="btn-check" onclick="checkPaymentStatus()">
                                        Cek
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            function checkPaymentStatus() {
                // console.log(transactionId);
                const modal = document.getElementById('qrCodeModal');
                const statusMessage = modal.querySelector('.status-message');
                const checkButton = modal.querySelector('#btn-check');

                // Disable the check button and show loading state
                checkButton.disabled = true;
                checkButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memeriksa...';

                // Assuming you have a way to get the current transaction ID
                 
                const transactionId = getCurrentTransactionId(); 
                console.log(transactionId);
                fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'check_payment_status',
                        transaction_id: transactionId
                    })
                })
                .then(response => response.json())
                .then(data => {
                        
                        checkButton.disabled = false;
                        checkButton.innerHTML = 'Cek';

                        if (data.success) {
                            switch (data.status) {
                                case 'settlement':
                                    statusMessage.innerHTML = '<div class="alert alert-success" role="alert">Pembayaran berhasil!</div>';
                                    setTimeout(() => {
                                        window.location.href = 'transberhasil.php'; // Redirect ke halaman sukses
                                    }, 2000);
                                    break;
                                    break;
                                case 'pending':
                                    statusMessage.innerHTML = '<div class="alert alert-warning" role="alert">Pembayaran masih dalam proses. Silakan coba cek lagi nanti.</div>';
                                    break;
                                case 'expire':
                                    statusMessage.innerHTML = '<div class="alert alert-danger" role="alert">Pembayaran telah kedaluwarsa. Silakan lakukan pemesanan ulang.</div>';
                                    break;
                                case 'cancel':
                                    statusMessage.innerHTML = '<div class="alert alert-danger" role="alert">Pembayaran dibatalkan. Silakan lakukan pemesanan ulang jika diperlukan.</div>';
                                    break;
                                default:
                                    statusMessage.innerHTML = '<div class="alert alert-info" role="alert">Status pembayaran: ' + data.status + '</div>';
                            }
                        } else {
                            statusMessage.innerHTML = '<div class="alert alert-danger" role="alert">Terjadi kesalahan: ' + data.message + '</div>';
                        }
                    })
                    .catch(error => {
                        checkButton.disabled = false;
                        checkButton.innerHTML = 'Cek';
                        statusMessage.innerHTML = '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat memeriksa status. Silakan coba lagi.</div>';
                        console.error('Error:', error);
                    });
            }

            function getCurrentTransactionId() {
                // Mencari modal QR code
                const modal = document.getElementById('qrCodeModal');

                if (!modal) {
                    console.error('Modal QR code tidak ditemukan');
                    return null;
                }

                // Mencoba mendapatkan ID transaksi dari atribut data
                const transactionId = modal.getAttribute('data-transaction-id');

                if (!transactionId) {
                    console.error('ID transaksi tidak ditemukan pada modal');
                    return null;
                }
                return modal.getAttribute('data-transaction-id');
                return transactionId;
            }

        </script>
</body>
</html>