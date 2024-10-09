<?php
require 'function.php';
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
        </style>
</head>

<body>
<div class="container-index">
        <div class="header-index">
            <h1>Product List</h1>
            <div class="container-button">
            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#keypadModal" style="position: absolute; right: 30px; top: 30px; background: none; border: none;">
                <i class="fas fa-lock" style="font-size: 20px; color: rgba(0, 0, 0, 0.2);"></i>
            </button>
            </div>
            <div class="content">
                <div class="product-list" id="product-list">
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p id="price-<?php echo $product['id']; ?>">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        <p id="description-<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['description']); ?></p>
                        <button onclick="showPaymentModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['discount']; ?>)">Buy</button>
                    </div>
                <?php endforeach; ?>
                </div>
                <div class="container-qrcode" style="display: contents;">
                    <div id="qrcode" class="qrcode"></div>
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
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="container-confirmation">
                        <div class="header-confirmation"></div>
                        <div class="voucher-form">
                            <button id="next-payment">Next Payment</button>
                            <div class="order-details-confirmation">
                                <h2 id="modal-price"></h2>
                            </div>
                            <form id="voucher-form" class="form-inline">
                                <input type="hidden" id="modal-product-id" name="product_id">
                                <input type="hidden" id="modal-product-name" name="product_name">
                                <input type="hidden" id="modal-product-price" name="product_price">
                                <input type="text" name="voucher_code" placeholder="Enter Voucher Code">
                                <button type="submit" class="apply-button">Apply Voucher</button>
                            </form>
                            <div id="voucher-message"></div>
                            <div class="footer-confirmation">
                                <div class="payment-logos">
                                    <img src="img/we-accept-the-payment.png" alt="method-payment">
                                </div>
                            </div>
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
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'login.php';
                        } else {
                            $('#keypadModal').modal('hide');
                            $('#errorModal').modal('show');
                            pinCode = '';
                            display.textContent = '';
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            }
        }

        // Add event listeners for keyboard input when the modal is open
        $('#keypadModal').on('shown.bs.modal', function () {
            $(document).on('keydown.keypad', function(event) {
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
            document.getElementById('modal-product-id').value = id;
            document.getElementById('modal-product-name').value = name;
            document.getElementById('modal-product-price').value = price;
            document.getElementById('modal-price').innerText = `IDR ${price}`;
            
            if (discount) {
                document.getElementById('voucher-form').style.display = 'contents';
            } else {
                document.getElementById('voucher-form').style.display = 'none';
            }

            $('#paymentModal').modal('show');
        }

        document.getElementById('next-payment').addEventListener('click', function() {
            const id = document.getElementById('modal-product-id').value;
            const name = document.getElementById('modal-product-name').value;
            const price = document.getElementById('modal-product-price').value;
            const voucherCode = document.querySelector('input[name="voucher_code"]')?.value || '';
            let updatedPrice = parseInt(document.getElementById('modal-price').innerText.replace('IDR ', ''));
            createTransaction(id, name, updatedPrice, price - updatedPrice, voucherCode);
        });

        document.getElementById('voucher-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const id = document.getElementById('modal-product-id').value;
            const name = document.getElementById('modal-product-name').value;
            const price = document.getElementById('modal-product-price').value;
            applyVoucher(id, name, price);
        });

        function applyVoucher(id, name, price) {
            const voucherCode = document.querySelector('input[name="voucher_code"]').value;
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: id,
                    product_name: name,
                    product_price: price,
                    voucher_code: voucherCode
                })
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('voucher-message');
                if (data.success) {
                    const discountedPrice = data.discounted_price;
                    document.getElementById('modal-price').innerText = `IDR ${discountedPrice}`;
                    messageDiv.innerHTML = `<p class="success">${data.message}</p>`;
                } else {
                    messageDiv.innerHTML = `<p class="alert">${data.message}</p>`;
                }
            });
        }

        function createTransaction(id, name, price, discount, voucherCode) {
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: id,
                    product_name: name,
                    product_price: price,
                    discount: discount,
                    total_price: price,
                    voucher_code: voucherCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.snap_url) {
                    $('#paymentModal').modal('hide');
                    const qrcodeDiv = document.createElement('div');
                    qrcodeDiv.innerHTML = `<iframe src="${data.snap_url}" width="75%"></iframe>`;
                    document.body.appendChild(qrcodeDiv);
                } else {
                    alert('Error: Unable to retrieve payment URL.');
                }
            });
        }
</script>
</body>
</html>