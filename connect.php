<?php
// function OpenCon()
// {
//     $dbhost = "localhost";
//     $dbuser = "root";
//     $dbpass = "";
//     $db = "CS5200Project";
//     $conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("Connect failed: %s\n" . $conn->error);
//     return $conn;
// }

function OpenCon()
{
    // Check if running on Google Cloud (App Engine)
    $on_gcp = (isset($_SERVER['GAE_ENV']) && $_SERVER['GAE_ENV'] === 'standard');

    if ($on_gcp) {
        // ✅ Running on Google App Engine - Use Unix socket for Cloud SQL
        $dbhost = null; // App Engine uses Unix socket instead of IP
        $dbsocket = "/cloudsql/db-002319129:us-west1:neu-test-jiadil"; // Replace with your Cloud SQL connection name
        $dbuser = "5200";    // Change to match Cloud SQL user
        $dbpass = "000000";  // Change to match Cloud SQL password
        $db = "CS5200Project";

        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db, null, $dbsocket);
    } else {
        // ✅ Running locally
        $use_gcp_proxy = true; // Toggle this if you want local MySQL instead

        if ($use_gcp_proxy) {
            // 🔹 Local Machine using Cloud SQL Proxy (127.0.0.1:3307)
            $dbhost = "127.0.0.1";  
            $dbport = "3307";        
            $dbuser = "5200";
            $dbpass = "000000";
        } else {
            // 🔹 Local XAMPP MySQL (localhost:3306)
            $dbhost = "localhost";
            $dbport = "3306";
            $dbuser = "root";
            $dbpass = "";
        }

        $db = "CS5200Project";
        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db, $dbport);
    }

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function CloseCon($conn)
{
    $conn->close();
}
