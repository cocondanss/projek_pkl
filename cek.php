<?php
if(isset($_SESSION['log'])){

} else {
    header('location:listproduct.php');
}

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: listproduct.php');
    exit;
}
