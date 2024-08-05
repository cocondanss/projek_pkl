<?php
// config.php

// Database configuration
$db_host = 'localhost';
$db_name = 'frame';
$db_user = 'root';
$db_pass = '';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Midtrans configuration
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-NE3uxo99mRjQRVTngJB0UOTd');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-abcde');

// Set true for production environment
define('IS_PRODUCTION', false);

require_once 'vendor/autoload.php';

\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = IS_PRODUCTION;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>



