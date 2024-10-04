<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .blur {
            filter: blur(5px);
        }
        .modal {
            position: fixed; 
            width: 100%; 
            height: 100%; 
            display: none; 
            justify-content: center; 
            align-items: center;
        }
        .modal-close {
            position: absolute; 
            background-color: rgba(0, 0, 0, 0.5); 
            width: 100%; 
            height: 100%; 
            display: flex; 
            justify-content: center; 
            align-items: center;
            z-index: 0;
        }

        .calculator {
            position: absolute;
            margin: 0 auto;
            width: 250px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            z-index: 1;
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
            background-color: #343a40;
            color: #ffffff;
        }
    </style>
</head>
        <i class="fas fa-lock" style="font-size: 24px; position: absolute; right: 20px; top: 20px; color: rgba(0, 0, 0, 0.3); cursor: pointer;" id="lock-icon"></i>
    <div class="container-index">
        <div class="content" id="content">
            <div class="product-list" id="product-list">
                <!-- Product items will be populated here -->
            </div>
            <div class="container-qrcode" style="display: contents;">
                <div id="qrcode" class="qrcode"></div>
            </div>
        </div>
    </div>
    
    <div class="modal" id="modal">
        <div class="modal-close" id="modalClose"></div>
        <div class="calculator" id="calculator">
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
                    <button class="btn btn-backspace" onclick="backspace()"> <i class="fas fa-arrow-left"></i></button>
                    <button class="btn btn-number" onclick="appendNumber('0')">0</button>
                    <button class="btn btn-enter" onclick="enter()"> <i class="fas fa-arrow-right"></i></button>
                    <button id="close-button">Close</button>
                </div>
            </div>
        </div>
    </div>
    </html>

    <script>
        let pinCode = '';
        let display = document.getElementById('display');
        let content = document.getElementById('content');
        let modal = document.getElementById('modal');
        let modalClose = document.getElementById('modalClose');
        let calculator = document.getElementById('calculator');
        const lockIcon = document.getElementById('lock-icon');
        // const closeButton = document.getElementById('close-button');
        // Tambahkan event listener untuk menutup modal ketika tombol close di klik
        
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
            
        modalClose.addEventListener('click', function() {
            modal.style.display = "none";
        });

        function appendNumber(number) {
            if (pinCode.length < 4) {
                pinCode += number;
                display.textContent = pinCode.replace(/./g, '*');
            }
        }

        function backspace() {
            pinCode = pinCode.slice(0, -1);
            display.textContent = pinCode.replace(/./g, '*');
        }

        function enter() {
            // TO DO: validate PIN code here
            alert('PIN code entered: ' + pinCode);
            pinCode = '';
            display.textContent = '';
            modal.style.display = 'none';
            content.classList.remove('blur');
        }

        lockIcon.addEventListener('click', function() {
            modal.style.display = 'flex';
            content.classList.add('blur');
        });

        // Add event listeners for keyboard input
        document.addEventListener('keydown', function(event) {
            if (event.key >= '0' && event.key <= '9' && pinCode.length < 4) {
                appendNumber(event.key);
            } else if (event.key === 'Backspace') {
                backspace();
            } else if (event.key === 'Enter') {
                enter();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            fetch('api.php')
                .then(response => response.json())
                .then(data => {
                    const productList = document.getElementById('product-list');
                    data.forEach(product => {
                        const productDiv = document.createElement('div');
                        productDiv.className = 'product';
                        productDiv.innerHTML = `
                            <h2>${product.name}</h2>
                            <p id="price-${product.id}">Price: Rp ${product.price}</p>
                            <form id="form-${product.id}" onsubmit="handleSubmit(event, ${product.discount}, ${product.id}, '${product.name}', ${product.price})">
                                <input type="hidden" name="product_id" value="${product.id}">
                                <input type="hidden" name="product_name" value="${product.name}">
                                <input type="hidden" name="product_price" value="${product.price}">
                                <button type="submit">Buy</button>
                            </form>
                        `;
                        productList.appendChild(productDiv);
                    });
                });
        });

        function handleSubmit(event, discount, id, name, price) {
            event.preventDefault();
            const qrcodeDiv = document.getElementById('qrcode');
            qrcodeDiv.innerHTML = `
                <div class="container-confirmation">
                    <div class="header-confirmation"></div>
                    <div class="voucher-form">
                        <button id="next-payment">Next Payment</button>
                        <div class="order-details-confirmation">
                            <h2 id="updated-price-${id}">IDR ${price}</h2>
                        </div>
                        <form id="voucher-form" class="form-inline" style="display:${discount ? 'contents' : 'none'};">
                            <input type="hidden" name="product_id" value="${id}">
                            <input type="hidden" name="product_name" value="${name}">
                            <input type="hidden" name="product_price" value="${price}">
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
            `;

            // Pasang kembali event listener setelah konten diperbarui
            document.getElementById('next-payment').addEventListener('click', function() {
                const voucherCode = document.querySelector('input[name="voucher_code"]')?.value || '';
                let updatedPrice = parseInt(document.getElementById(`updated-price-${id}`).innerText.replace('IDR ', ''));
                createTransaction(id, name, updatedPrice, discount, voucherCode);
            });

            if (discount) {
                document.getElementById('voucher-form').addEventListener('submit', function(event) {
                    event.preventDefault();
                    applyVoucher(id, name, price);
                });
            }
        }

        function applyVoucher(id, name, price) {
            const voucherCode = document.querySelector('input[name="voucher_code"]').value;
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'apply_voucher',
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
                    document.getElementById(`updated-price-${id}`).innerText = `IDR ${discountedPrice}`;
                    document.getElementById(`price-${id}`).innerText = `Price: Rp ${discountedPrice}`;
                    messageDiv.innerHTML = `<p class="success">${data.message}</p>`;
                } else {
                    messageDiv.innerHTML = `<p class="alert">${data.message}</p>`;
                }
            });
        }

        function createTransaction(id, name, price, discount, voucherCode) {
            let discountedPrice = price;
            if (voucherCode) {
                discountedPrice = parseInt(document.getElementById(`updated-price-${id}`).innerText.replace('IDR ', '')) || price;
            }

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'create_transaction',
                    product_id: id,
                    product_name: name,
                    product_price: price,
                    discount: price - discountedPrice,
                    total_price: discountedPrice
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.snap_url) {
                    const qrcodeDiv = document.getElementById('qrcode');
                    qrcodeDiv.innerHTML = `<iframe src="${data.snap_url}" width="75%"></iframe>`;
                } else {
                    alert('Error: Unable to retrieve payment URL.');
                }
            });
        }
    </script>
</body>
</html>