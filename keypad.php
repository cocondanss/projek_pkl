<?php
session_start();
// Koneksi ke database
$conn = mysqli_connect("localhost", "u529472640_root", "Daclen123", "u529472640_framee");


function cek_pin($pin, $type = 'admin') {
    global $conn;
    
    if ($type === 'success') {
        // Cek PIN dari tabel success_pin
        $stmt = $conn->prepare("SELECT pin FROM success_pin WHERE id = 1");
        $stmt->execute();
    } else {
        // Cek PIN admin dari tabel settings
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'keypad_pin'");
        $stmt->execute();
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    error_log("Checking PIN: $pin for type: $type");
    error_log("Stored PIN value: " . ($row ? ($type === 'success' ? $row['pin'] : $row['setting_value']) : 'not found'));
    
    return $pin === ($type === 'success' ? $row['pin'] : $row['setting_value']);
}

// Fungsi untuk login
function login($pin) {
    return cek_pin($pin);
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'];
    $type = $_POST['type'] ?? 'admin';
    
    error_log("Received request - PIN: $pin, Type: $type");
    
    if (cek_pin($pin, $type)) {
        if ($type === 'admin') {
            $_SESSION['log'] = 'true';
        } else if ($type === 'success') {
            $_SESSION['success_page_access'] = true;
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid PIN']);
    }
    exit;
}