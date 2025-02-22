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

    if (empty($voucherCode)) {
        return $price;
    }
    
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Jika voucher sekali pakai, cek apakah sudah digunakan
        if ($row['one_time_use'] == 1 && $row['used_at'] !== null) {
            // Tambahkan buffer waktu 30 detik untuk memastikan transaksi bisa selesai
            $bufferTime = 20; // dalam detik
            $usedTime = strtotime($row['used_at']);
            
            if (time() - $usedTime > $bufferTime) {
                return $price; // Voucher sudah expired
            }
        }

        // Jika sampai di sini, berarti voucher masih valid
        $discountAmount = $row['discount_amount'];
        
        if ($discountAmount <= 100) {
            $discountedPrice = $price - ($price * ($discountAmount / 100));
        } else {
            $discountedPrice = $price - $discountAmount;
        }

        return max(0, $discountedPrice);
    }

    return $price;
}

// Inisialisasi variabel untuk sistem voucher
$voucherMessages = [];
$voucherCode = '';
$originalPrice = 0; // Inisialisasi harga asli
$discountedPrice = 0; // Inisialisasi harga diskon

// Proses pengecekan voucher saat ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['voucher_code'])) {
    $voucherCode = trim($_POST['voucher_code']);
    
    $stmt = $conn->prepare("SELECT * FROM vouchers2 WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        date_default_timezone_set('Asia/Jakarta');
        $currentDateTime = date('Y-m-d H:i:s');
        
        if ($row['one_time_use'] == 1) {
            // Untuk voucher sekali pakai
            if ($row['used_at'] === null) {
                $updateStmt = $conn->prepare("UPDATE vouchers2 SET used_at = ? WHERE code = ?");
                $updateStmt->bind_param("ss", $currentDateTime, $voucherCode);
                
                if ($updateStmt->execute()) {
                    $_SESSION['active_voucher'] = $voucherCode;
                    $_SESSION['voucher_applied_time'] = $currentDateTime;
                    $voucherMessages[] = "<p class='voucher-message success'>Voucher berhasil digunakan.</p>";
                } else {
                    $voucherMessages[] = "<p class='voucher-message error'>Gagal mencatat penggunaan voucher.</p>";
                }
            } else {
                $voucherMessages[] = "<p class='voucher-message error'>Voucher ini hanya sekali pakai.</p>";
            }
        } else {
            // Untuk voucher yang bisa digunakan berkali-kali
            $updateStmt = $conn->prepare("UPDATE vouchers2 SET used_at = ? WHERE code = ?");
            $updateStmt->bind_param("ss", $currentDateTime, $voucherCode);
            
            if ($updateStmt->execute()) {
                $_SESSION['active_voucher'] = $voucherCode;
                $_SESSION['voucher_applied_time'] = $currentDateTime;
                $voucherMessages[] = "<p class='voucher-message success'>Voucher berhasil digunakan.</p>";
            } else {
                $voucherMessages[] = "<p class='voucher-message error'>Gagal mencatat penggunaan voucher.</p>";
            }
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

$backgroundFile = file_get_contents('config/background.txt');
$backgroundType = file_get_contents('config/background_type.txt');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Product</title>
    <link rel="stylesheet" href="css/style4.css">
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styleLP2.css?v=1.6">
    <style>
    body {
    <?php if ($backgroundType == 'image'): ?>
        background-image: url('<?php echo $backgroundFile; ?>') !important;
        background-size: cover !important;
        background-repeat: no-repeat !important;
        background-position: center center !important;
    <?php elseif ($backgroundType == 'video'): ?>
        /* Video background styles */
    <?php endif; ?>
    }
    </style>
</head>
<body>
<?php if ($backgroundType == 'video'): ?>
        <video autoplay muted loop id="backgroundVideo" style="position: fixed; right: 0; bottom: 0; min-width: 100%; min-height: 100%;">
            <source src="<?php echo $backgroundFile; ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="container-index" style="max-width: 100%;">
        <div class="header-index">
            <div class="container-button">
                <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#keypadModal"
                    style="position: absolute; right: 30px; top: 30px; background: none; border: none;">
                    <i class="fas fa-lock" style="font-size: 20px; color: #FFD700;"></i>
                </button>
            </div>
            <div class="product-container">
                <div class="row">
                    <div class="product-list" style="background: none;" id="product-list">
                    <?php foreach ($produk as $item): 
                        $originalPrice = $item['price'];
                        // Hitung harga diskon berdasarkan voucher yang ada
                        $discountedPrice = applyVoucher($voucherCode, $originalPrice);             
                    ?>
                        <div class="product product-<?php echo $item['id']; ?>" data-product-id="<?php echo $item['id']; ?>">
                            <div class="card-body"> 
                                <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                                <div class="price-container">
                                    <?php if ($discountedPrice < $originalPrice): ?>
                                        <p class="original-price">Rp <span><?php echo number_format($originalPrice, 0, ',', '.'); ?>,00</span></p>
                                        <p class="discounted-price">Rp <span><?php echo number_format($discountedPrice, 0, ',', '.'); ?>,00</span></p>
                                    <?php else: ?>
                                        <p>Rp <span><?php echo number_format($originalPrice, 0, ',', '.'); ?>,00</span></p>
                                    <?php endif; ?>
                                </div>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <button onclick="showPaymentModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', <?php echo number_format($discountedPrice, 0, '', ''); ?>)">Buy</button>                            
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
                                window.location.href = response.redirect; // Use the redirect URL from response
                            } else {
                                $('#keypadModal').modal('hide');
                                $('#errorModal').modal('show');
                            }
                            pinCode = '';
                            display.textContent = '';
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

            function showPaymentModal(id, name, price, discount = 0) {
                console.log('ShowPaymentModal called with:', { id, name, price, discount }); // Debug log

                // Validasi parameter
                if (!id || !name || price === undefined) {
                    console.error('Parameter tidak valid:', { id, name, price });
                    return;
                }

                // Pastikan price adalah number dan valid
                price = parseFloat(price);
                if (isNaN(price)) {
                    console.error('Harga tidak valid:', price);
                    return;
                }

                // Jika harga adalah 0 atau kurang, langsung proses sebagai transaksi gratis
                if (price <= 0) {
                    fetch('api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'create_free_transaction',
                            product_id: id,
                            product_name: name,
                            product_price: 0
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'transberhasil.php';
                        } else {
                            alert('Error: ' + (data.message || 'Gagal memproses transaksi gratis.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memproses transaksi.');
                    });
                    return;
                }

                // Jika harga lebih dari 0, lanjutkan dengan proses normal
                createTransaction(id, name, price, discount)
                    .then(response => {
                        console.log('Create Transaction Response:', response);
                        if (response && response.success) {
                            const existingModal = document.getElementById('qrCodeModal');
                            if (existingModal) existingModal.remove();

                            // Update struktur modal HTML dengan progress bar yang langsung aktif
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
                                                    <img id="qrCodeImage" src="${response.qr_code_url}" alt="QR Code" class="qr-code-image">
                                                </div>
                                                <div id="countdown" class="text-center mb-3"></div>
                                                <div class="payment-status-container">
                                                    <div class="progress mb-3" style="height: 10px; max-width: 500%; margin: 0 auto;">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                             role="progressbar" 
                                                             style="width: 100%; font-size: 14px;" 
                                                             id="payment-progress-bar">
                                                             Menunggu Pembayaran...
                                                        </div>
                                                    </div>
                                                    <div class="status-message text-center"></div>
                                                </div>
                                                <div class="button-container text-center mt-3">
                                                    <button type="button" class="btn btn-dark mr-2" id="btn-cancel" onclick="cancelTransaction()">
                                                        Batal
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            document.body.insertAdjacentHTML('beforeend', modalHTML);

                            const qrCodeModal = document.getElementById('qrCodeModal');
                            qrCodeModal.setAttribute('data-transaction-id', response.order_id);

                            startCountdown(900);
                            const bootstrapModal = new bootstrap.Modal(qrCodeModal);
                            bootstrapModal.show();

                            // Mulai pengecekan status pembayaran
                            startPaymentCheck();
                        }
                    })
                    .catch(error => {
                        console.error('Error in createTransaction:', error);
                        alert('Terjadi kesalahan saat membuat transaksi.');
                    });
            }

            // Update fungsi startPaymentCheck
            function startPaymentCheck() {
                const modal = document.getElementById('qrCodeModal');
                const statusMessage = modal.querySelector('.status-message');
                const progressBar = modal.querySelector('#payment-progress-bar');
                const transactionId = getCurrentTransactionId();

                function checkStatus() {
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
                        if (data.success) {
                            switch (data.status) {
                                case 'settlement':
                                    progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                                    progressBar.classList.add('bg-success');
                                    progressBar.textContent = 'Pembayaran Berhasil!';
                                    // statusMessage.innerHTML = '<div class="alert alert-success" role="alert">Pembayaran berhasil!</div>';
                                    clearInterval(checkInterval);
                                    setTimeout(() => {
                                        window.location.href = 'transberhasil.php';
                                    }, 2000);
                                    break;
                                case 'expire':
                                    progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                                    progressBar.classList.add('bg-danger');
                                    progressBar.textContent = 'Pembayaran Kedaluwarsa';
                                    clearInterval(checkInterval);
                                    setTimeout(() => {
                                        window.location.href = 'transbatal.php';
                                    }, 1000);
                                    break;
                                case 'cancel':
                                    progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                                    progressBar.classList.add('bg-danger');
                                    progressBar.textContent = 'Pembayaran Dibatalkan';
                                    clearInterval(checkInterval);
                                    setTimeout(() => {
                                        window.location.href = 'transbatal.php';
                                    }, 1000);
                                    break;
                                case 'pending':
                                    // Tetap animasi berjalan selama pending
                                    progressBar.textContent = 'Menunggu Pembayaran...';
                                    break;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }

                // Cek setiap 3 detik
                const checkInterval = setInterval(checkStatus, 3000);
                modal.setAttribute('data-check-interval', checkInterval);
                
                // Cek pertama kali
                checkStatus();
            }

            // Update fungsi cancelTransaction untuk redirect langsung
            function cancelTransaction() {
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
                        // Langsung redirect ke transbatal.php
                        window.location.href = 'transbatal.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Tetap redirect meskipun ada error
                    window.location.href = 'transbatal.php';
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

            document.addEventListener('DOMContentLoaded', function() {
                const voucherForm = document.getElementById('voucher-form');
                if (voucherForm) {
                    voucherForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Disable form selama proses
                        const submitButton = this.querySelector('button[type="submit"]');
                        const originalButtonText = submitButton.innerHTML;
                        submitButton.disabled = true;
                        submitButton.innerHTML = 'Memproses...';
                        
                        fetch('voucher.php', {
                            method: 'POST',
                            body: new FormData(this),
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Update konten halaman
                            document.documentElement.innerHTML = html;
                            
                            // Tampilkan pesan status
                            const messageContainer = document.getElementById('voucher-message-container');
                            if (messageContainer) {
                                messageContainer.style.display = 'block';
                                setTimeout(() => {
                                    messageContainer.style.display = 'none';
                                }, 3000);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat memproses voucher');
                        })
                        .finally(() => {
                            // Re-enable form
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalButtonText;
                        });
                    });
                }
            });
            
        </script>
</body>
</html>