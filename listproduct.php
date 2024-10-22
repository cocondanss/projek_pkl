<?php
require 'function.php';

// Modify the query to only fetch visible products
$query = "SELECT * FROM products WHERE visible = 1";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
            margin-top: auto;
        }

        .product button:hover {
            background-color: #b0b0b0;
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
            <div class="content">
                <div class="product-list" id="product-list">
                    <?php foreach ($products as $product): ?>
                        <div class="product">
                            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p id="price-<?php echo $product['id']; ?>">Rp
                                <?php echo number_format($product['price'], 0, ',', '.'); ?>
                            </p>
                            <p id="description-<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>
                            <button onclick="showPaymentModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['discount']; ?>)">Buy</button>
                        </div>
                    <?php endforeach; ?>
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
                                <button class="btn btn-backspace" onclick="backspace()"><i
                                        class="fas fa-backspace"></i></button>
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

                        // Tambahkan modal ke body
                        document.body.insertAdjacentHTML('beforeend', modalHTML);
                        // Dapatkan referensi ke modal yang baru dibuat
                        const qrCodeModal = document.getElementById('qrCodeModal');

                        // Set atribut data-transaction-id
                        qrCodeModal.setAttribute('data-transaction-id', response.order_id);

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