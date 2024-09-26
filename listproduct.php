<!doctype html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <a href="produk.php" class="button"><button>Kembali</button></a>
    <div class="container-index">
        <div class="header-index">
            <h1>Product List</h1>
        </div>
        <div class="content">   
            <div class="product-list" id="product-list">
                <!-- Product items will be populated here -->
            </div>
            <div class="container-qrcode" style="display: contents;">
                <div id="qrcode" class="qrcode"></div>
            </div>
        </div>
    </div>
    <script>
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

        // Tambahkan tombol "Buy" lagi setelah pembayaran selesai
        function handleSubmit(event, discount, id, name, price) {
        event.preventDefault();
        const productDiv = document.getElementById(`product-${id}`);
        const form = document.getElementById(`form-${id}`);

        // Tambahkan tombol "Buy" lagi setelah pembayaran selesai
        productDiv.innerHTML = `
            <h2>${name}</h2>
            <p id="price-${id}">Price: Rp ${price}</p>
            <form id="form-${id}" onsubmit="handleSubmit(event, ${discount}, ${id}, '${name}', ${price})">
                <input type="hidden" name="product_id" value="${id}">
                <input type="hidden" name="product_name" value="${name}">
                <input type="hidden" name="product_price" value="${price}">
                <button type="submit">Buy</button>
            </form>
        `;

        // Pasang kembali event listener setelah konten diperbarui
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const voucherCode = document.querySelector('input[name="voucher_code"]')?.value || '';
            let updatedPrice = parseInt(document.getElementById(`updated-price-${id}`).innerText.replace('IDR ', ''));
            createTransaction(id, name, updatedPrice, discount, voucherCode);
        });
    }
        if (discount) {
            document.getElementById('voucher-form').addEventListener('submit', function(event) {
                event.preventDefault();
                applyVoucher(id, name, price);
            });
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
