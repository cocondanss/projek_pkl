<?php
require 'function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $visible = $_POST['visible'];
    
    // Prepare statement untuk mencegah SQL injection
    $stmt = $conn->prepare("UPDATE products SET visible = ? WHERE id = ?");
    $stmt->bind_param("ii", $visible, $product_id);
    
    $response = array();
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Visibility updated successfully';
    } else {
        $response['success'] = false;
        $response['error'] = $stmt->error;
    }
    
    echo json_encode($response);
    exit;
}