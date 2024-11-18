<?php
session_start();
// Koneksi ke database
$conn = mysqli_connect("localhost", "u529472640_root", "Daclen123", "u529472640_framee");

function cek_pin($pin, $type = 'admin') {
    global $conn;
    $key = ($type === 'success') ? 'success_pin' : 'keypad_pin';
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $pin === $row['setting_value'];
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'];
    $action = $_POST['action'] ?? 'check_admin_pin';
    
    if ($action === 'check_success_pin') {
        // Check PIN for transaksiberhasil.php access
        $isValid = cek_pin($pin, 'success');
    } else {
        // Check admin PIN
        $isValid = cek_pin($pin, 'admin');
    }
    
    echo json_encode(['success' => $isValid]);
    exit;
}