<?php
require_once 'config/database.php';
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
