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
    
    if ($row && $pin === $row['setting_value']) {
        if ($type === 'success') {
            $_SESSION['success_page_access'] = true;
        } else {
            $_SESSION['log'] = 'true';
        }
        return true;
    }
    return false;
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'];
    $type = $_POST['type'] ?? 'admin';
    
    if (cek_pin($pin, $type)) {
        echo json_encode([
            'success' => true,
            'redirect' => $type === 'success' ? 'transaksiberhasil.php' : 'login.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'PIN tidak valid'
        ]);
    }
    exit;
}