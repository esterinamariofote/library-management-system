<?php
// HTTP Basic Auth - asks for password EVERY time
$auth_user = 'admin';
$auth_pass = 'admin123';

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != $auth_user || $_SERVER['PHP_AUTH_PW'] != $auth_pass) {
    header('WWW-Authenticate: Basic realm="Management Access"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Access denied.";
    exit();
}
?>
