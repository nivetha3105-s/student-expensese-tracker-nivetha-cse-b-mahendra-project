<?php
require_once 'config/database.php';
$uid = require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?')->execute([(int)($_POST['id'] ?? 0), $uid]);
    flash('success', 'Transaction deleted.');
}
redirect('/dashboard.php' . (preg_match('/^\d{4}-\d{2}$/', $_POST['month'] ?? '') ? '?month=' . $_POST['month'] : ''));
