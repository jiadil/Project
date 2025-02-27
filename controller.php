<?php 
if($_SERVER['REQUEST_URI'] == '/'){
    require('index.php');
    return;
}

$regex = '/.+\.php/';
if (preg_match($regex, $_SERVER['REQUEST_URI'], $matches)) {
    $file_path = __DIR__ . $matches[0];
    if (file_exists($file_path)) {
        require($file_path);
        return;
    }
}

http_response_code(404);
exit('Not Found');

?>