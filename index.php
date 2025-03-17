<?php
// Start session first, before any output
session_start();

// Then set error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include files after session started
$connect_path = __DIR__ . '/strata/connect.php';
include $connect_path;

$check_connection_path = __DIR__ . '/strata/check-connection.php';
include $check_connection_path;
?>