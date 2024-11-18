<?php
session_start();
// Koneksi ke database
$conn = mysqli_connect("localhost", "u529472640_root", "Daclen123", "u529472640_framee");


function cek_pin($pin, $type = 'admin') {
    global $conn;
    $key = ($type === 'success') ? 'success_page_pin' : 'keypad_pin';
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    error_log("Checking PIN: $pin for type: $type with key: $key");
    error_log("Stored PIN value: " . ($row ? $row['setting_value'] : 'not found'));
    
    return $pin === $row['setting_value'];
}

// Fungsi untuk login
function login($pin) {
    return cek_pin($pin);
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'];
    $type = $_POST['type'] ?? 'admin'; // Default ke admin jika tidak ada type
    
    error_log("Received request - PIN: $pin, Type: $type");
    
    if (cek_pin($pin, $type)) {
        if ($type === 'admin') {
            $_SESSION['log'] = 'true'; // Set session untuk admin login
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid PIN']);
    }
    exit;
}