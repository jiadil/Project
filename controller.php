<?php 
if ($_SERVER['REQUEST_URI'] == '/') {
    require __DIR__ . '/index.php';
    return;
}

// Extract the PHP file path from the request
$regex = '/\/([a-zA-Z0-9-_\/]+\.php)/'; // Allow subdirectories
if (preg_match($regex, $_SERVER['REQUEST_URI'], $matches)) {
    $requested_file = $matches[1];

    // Try loading from root directory
    $file_path = __DIR__ . '/' . $requested_file;
    if (file_exists($file_path)) {
        require $file_path;
        return;
    }

    // Try loading from strata folder
    $strata_path = __DIR__ . '/strata/' . $requested_file;
    if (file_exists($strata_path)) {
        require $strata_path;
        return;
    }
}

// If no file found, return 404
http_response_code(404);
exit('Not Found');
?>
