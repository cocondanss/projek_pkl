<?php
require 'function.php';

// Modify the query to only fetch visible products
$query = "SELECT * FROM products WHERE visible = 0 ";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

function applyVoucher($voucherCode, $price) {
    global $conn;
    
    $debug_info = "Voucher Code: $voucherCode, Original Price: $price\n";

    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $debug_info .= "Voucher found: " . print_r($row, true) . "\n";

        $discountAmount = $row['discount_amount'];
        $debug_info .= "Discount Amount: $discountAmount\n";

        $discountedPrice = $price - $discountAmount;
        
        $debug_info .= "Calculated Discounted Price: $discountedPrice\n";

        $finalPrice = max($discountedPrice, 0); // Ensure price is not negative
        
        // Jika Anda ingin menyimpan informasi debug, Anda bisa menggunakan logging
        // error_log($debug_info);
        
        return $finalPrice;
    }

    $debug_info .= "No voucher found\n";
    // error_log($debug_info);
    
    return $price;
}


    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    if ($stmt === false) {
        $debug_info .= "Failed to prepare statement: " . $conn->error . "\n";
        error_log($debug_info);
    $debug_info .= "No voucher found\n";
    // error_log($debug_info);
    
    return $price;
}
// Inisialisasi array untuk menyimpan pesan voucher
$voucherMessages = [];
$voucherCode = '';
// Jika ada permintaan POST untuk voucher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    $voucherCode = $_POST['voucher_code'];
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['one_time_use'] == 1 && $row['used_at'] !== null) {
            $voucherMessages[] = "<p class='voucher-message error'>Voucher hanya dapat digunakan sekali</p>";
        } else {
            // Proses penerapan voucher
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

// Ambil data produk
$produk = mysqli_query($conn, "SELECT * FROM products WHERE visible = 1 ");
if (!$produk) {
    die("Query gagal: " . mysqli_error($conn));
}

// Jika ini adalah permintaan AJAX, hanya render bagian daftar produk
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
            border-radius: 10px;
            padding: 20px;
            width: 300px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .product {
            background-color: #2b2d42;
            color: white;
            border-radius: 10px;
            padding: 20px;
            width: 300px;
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

        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
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
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            border: #28a745;
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
            overflow: hidden;
        }

        .qr-modal .modal-body {
            padding: 30px;
            text-align: center;
        }

        .qr-modal .modal-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .qr-modal .qr-code-container {
            background-color: #ffffff;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .qr-modal .qr-code-image {
            max-width: 200px;
            height: auto;
        }

        .qr-modal .qr-instructions {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .qr-modal .btn-cancel {
            background-color: #2b3242;
            font-size: 110%;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 120px;
            margin-right: 30%;
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
            width: 107%;
            padding: 20px;
            margin-bottom: 15px;
            /* border: 1px solid #ccc;*/
            border-radius: 5px;
            
        }

        #product-list {
            position: relative;
            top: 0px;
            left: 0px;
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
            background-color: #ffecec;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .voucher-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container-index">
        <div class="header-index">
            <div class="container-button">
                <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#keypadModal"
                    style="position: absolute; right: 30px; top: 30px; background: none; border: none;">
                    <i class="fas fa-lock" style="font-size: 20px; color: rgba(0, 0, 0, 0.2);"></i>
                </button>
            </div>
            <div class="product-container">
                <div class="product-list" id="product-list">
                    <?php foreach ($produk as $item): 
                        $originalPrice = $item['price'];
                        $discountedPrice = applyVoucher($voucherCode, $originalPrice);
                    ?>
                        <div class="product" data-product-id="<?php echo $item['id']; ?>">
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
                            <button onclick="showPaymentModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', <?php echo $originalPrice; ?>)">Buy</button>
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
                            <input type="text" name="voucher_code" placeholder="Masukkan kode voucher">
                            <button type="submit">Terapkan Voucher</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <div class="modal fade" id="keypadModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
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

            function showAndHideMessage() {
                var messageContainer = document.getElementById('voucher-message-container');
                if (messageContainer) {
                    <?php
                    if (isset($_SESSION['voucher_message'])) {
                        echo "messageContainer.innerHTML = '" . $_SESSION['voucher_message'] . "';";
                        echo "messageContainer.style.display = 'block';";
                        unset($_SESSION['voucher_message']); // Hapus pesan dari session
                    }
                    ?>
                    
                    if (messageContainer.innerHTML.trim() !== '') {
                        setTimeout(function() {
                            $(messageContainer).fadeOut(500, function() {
                                messageContainer.innerHTML = '';
                            });
                        }, 3000); // Pesan akan hilang setelah 3 detik
                    }
                }
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
                            }, 1000);
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

            let pinCode = '';
            let display = document.getElementById('display');

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

            // document.addEventListener('DOMContentLoaded', function() {
            //     fetch('api.php')
            //         .then(response => response.json())
            //         .then(data => {
            //             const productList = document.getElementById('product-list');
            //             data.forEach(product => {
            //                 const productDiv = document.createElement('div');
            //                 productDiv.className = 'product';
            //                 productDiv.innerHTML = `
            //                     <h2>${product.name}</h2>
            //                     <p id="price-${product.id}">Price: Rp ${product.price}</p>
            //                     <form id="form-${product.id}" onsubmit="handleSubmit(event, ${product.discount}, ${product.id}, '${product.name}', ${product.price})">
            //                         <input type="hidden" name="product_id" value="${product.id}">
            //                         <input type="hidden" name="product_name" value="${product.name}">
            //                         <input type="hidden" name="product_price" value="${product.price}">
            //                         <button type="submit">Buy</button>
            //                     </form>
            //                 `;
            //                 productList.appendChild(productDiv);
            //             });
            //         });
            // });

            function showPaymentModal(id, name, price) {
                // Get the actual displayed price (which may be discounted)
                const productElement = document.querySelector(`[data-product-id="${id}"]`);
                let finalPrice = price;
                
                if (productElement) {
                    const discountedPriceElement = productElement.querySelector('.discounted-price span');
                    if (discountedPriceElement) {
                        // Remove 'Rp ' and '.', then parse as integer
                        finalPrice = parseInt(discountedPriceElement.textContent.replace(/[Rp\s\.]/g, ''));
                    }
                }

                if (id && name && finalPrice !== undefined) {
                    createTransaction(id, name, finalPrice)
                        .then(response => {
                            if (response.success) {
                                const existingModal = document.getElementById('qrCodeModal');
                                if (existingModal) {
                                    existingModal.remove();
                                }
                        // Buat elemen modal baru
                        const modalHTML = `
                        <div class="modal fade qr-modal" id="qrCodeModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="modal-title">
                                            <img src="img/Logo_QRIS-removebg-preview.png" alt="QRIS Logo" style="max-width: 20%; height: auto;">
                                        </div>
                                        <div class="qr-code-container">
                                            <img src="${response.qr_code_url}" alt="QR Code" class="qr-code-image">
                                        </div>
                                        <p class="qr-instructions">*scan QR code ini untuk melakukan pembayaran</p>
                                        <div style="display: flex; justify-content: center;">
                                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Batal</button>
                                            <button type="button" onclick="checkPaymentStatus()"  class="btn" id="btn-check">Cek</button>
                                            <button type="button"   class="btn btn-check" >Cek</button>
                                        </div>
                                        <div class="status-message mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `

                        document.body.insertAdjacentHTML('beforeend', modalHTML);
                        const qrCodeModal = document.getElementById('qrCodeModal');
                        qrCodeModal.setAttribute('data-transaction-id', response.order_id);
                        const modalInstance = new bootstrap.Modal(qrCodeModal);
                        modalInstance.show();
                    } else {
                        alert('Error: ' + response.message);
                    }
                })
                .catch(error => {
                    console.error('Error in createTransaction:', error);
                    alert('Terjadi kesalahan saat membuat transaksi.');
                });
        } else {
            console.error('Invalid parameters');
        }
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
                                        const qrCodeModal = bootstrap.Modal.getInstance(modal);
                                        qrCodeModal.hide();
                                        // Optionally, refresh the page or update the UI
                                        // window.location.reload();
                                    }, 2000);
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