<?php
// ---- Database connection (ByteXL MySQL workspace) ----
$host     = "db01.dbhost.dev";
$port     = 5051;
$dbname   = "db_455cek9jg";
$username = "user_455cek9jg";
$password = "p455cek9jg";

// BASE_URL is a RELATIVE prefix ('.' , '..', '../..') worked out from the folder
// of the running script. It never contains /proxy/8080, so links and redirects
// work on any URL: ...-8080.ws6.app, ...-5300.ws6.app/proxy/8080, localhost, XAMPP.
if (!defined('BASE_URL')) {
    $root  = str_replace('\\', '/', (string)realpath(__DIR__ . '/..'));
    $dir   = str_replace('\\', '/', (string)realpath(dirname($_SERVER['SCRIPT_FILENAME'] ?? '')));
    $rel   = (str_starts_with($dir, $root)) ? trim(substr($dir, strlen($root)), '/') : '';
    $depth = $rel === '' ? 0 : substr_count($rel, '/') + 1;
    define('BASE_URL', $depth === 0 ? '.' : implode('/', array_fill(0, $depth, '..')));
}
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Sessions + helper functions (is_logged_in, redirect, e, render_header ...)
require_once __DIR__ . '/functions.php';
