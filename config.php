<?php
// config.php

// Database configuration
$db_host = 'localhost';
$db_name = 'u529472640_framee';
$db_user = 'u529472640_root';
$db_pass = 'Daclen123';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get Midtrans settings from database
$stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('midtrans_server_key', 'midtrans_client_key', 'midtrans_is_production')");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

define('MIDTRANS_SERVER_KEY', $settings['midtrans_server_key']);
define('MIDTRANS_CLIENT_KEY', $settings['midtrans_client_key']);
define('IS_PRODUCTION', $settings['midtrans_is_production'] === '1');

require_once 'vendor/autoload.php';

\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = IS_PRODUCTION;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;



