<?php
session_start();
// Koneksi ke database
$conn = mysqli_connect("localhost", "u529472640_root", "Daclen123", "u529472640_framee");

function cek_pin($pin) {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'keypad_pin'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $pin === $row['setting_value'];
}

// Tambahkan fungsi baru untuk cek success page pin
function cek_success_pin($pin) {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'success_page_pin'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $pin === $row['setting_value'];
}

// Fungsi untuk login
function login($pin) {
    return cek_pin($pin);
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'];
    
    // Cek success page pin terlebih dahulu
    if (cek_success_pin($pin)) {
        echo json_encode(['success' => true, 'redirect' => 'transaksiberhasil.php']);
        exit;
    }
    
    // Jika bukan success pin, cek untuk login seperti biasa
    if (cek_pin($pin)) {
        if (login($pin)) {
            echo json_encode(['success' => true, 'redirect' => 'login.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Login failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid PIN']);
    }
    exit;
}