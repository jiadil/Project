<?php
// Test message to see if PHP is executing at all
// echo "PHP is running - Debug point 1<br>";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// echo "Debug point 2 - After error settings<br>";

// Check if connection file exists
if (!file_exists('strata/connect.php')) {
    echo "Error: connect.php file not found<br>";
    echo "Current directory: " . __DIR__ . "<br>";
    echo "Files in current directory:<br>";
    echo "<pre>";
    print_r(scandir(__DIR__));
    echo "</pre>";
    exit;
}

// echo "Debug point 3 - connect.php exists<br>";

// Include check-connection.php file
if (file_exists('strata/check-connection.php')) {
    // echo "Debug point 4 - About to include check-connection.php<br>";
    include 'strata/check-connection.php';
    // echo "Debug point 5 - After including check-connection.php<br>";
} else {
    echo "Error: check-connection.php file not found";
}
?>