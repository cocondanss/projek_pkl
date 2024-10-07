<?php
session_start();
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "framee");


// Fungsi untuk cek pin
function cek_pin($pin) {
    global $conn;
    $query = "SELECT * FROM pin WHERE pin = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Fungsi untuk login
function login($pin) {
    return cek_pin($pin);
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'];
    if (cek_pin($pin)) {
        if (login($pin)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Login failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid PIN']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Keypad</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f8f9fa;
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
        <div class="calculator-container">
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
            <a href="listproduct.php" class="btn btn-secondary back-button">
                Kembali
            </a>
        </div>

            <!-- Modal for incorrect PIN -->
            <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Error</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            The PIN you entered is incorrect.
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
            </script>
    </body>
</html>