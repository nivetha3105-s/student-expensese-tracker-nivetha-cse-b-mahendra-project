<?php
require_once 'config/database.php';
$uid = require_login();
$d = ['type' => $_GET['type'] ?? 'expense', 'category' => 'Food', 'amount' => '', 'description' => '', 'txn_date' => date('Y-m-d')];
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $err] = read_txn();
    if (!$err) {
        $pdo->prepare('INSERT INTO transactions (user_id, type, category, amount, description, txn_date) VALUES (?,?,?,?,?,?)')
            ->execute([$uid, $d['type'], $d['category'], $d['amount'], $d['description'], $d['txn_date']]);
        flash('success', ucfirst($d['type']) . ' added.');
        redirect('/dashboard.php?month=' . substr($d['txn_date'], 0, 7));
    }
}
render_header('Add transaction');
?>
<div class="form-box"><h2>Add transaction</h2><?php txn_form($d, $err, 'Save'); ?></div>
<?php render_footer(); ?>
