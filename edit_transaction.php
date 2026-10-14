<?php
require_once 'config/database.php';
$uid = require_login();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM transactions WHERE id = ? AND user_id = ?');
$s->execute([$id, $uid]);
$t = $s->fetch();
if (!$t) { flash('error', 'Transaction not found.'); redirect('/dashboard.php'); }

$d = $t; $err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $err] = read_txn();
    if (!$err) {
        $pdo->prepare('UPDATE transactions SET type=?, category=?, amount=?, description=?, txn_date=? WHERE id=? AND user_id=?')
            ->execute([$d['type'], $d['category'], $d['amount'], $d['description'], $d['txn_date'], $id, $uid]);
        flash('success', 'Transaction updated.');
        redirect('/dashboard.php?month=' . substr($d['txn_date'], 0, 7));
    }
}
render_header('Edit transaction');
?>
<div class="form-box"><h2>Edit transaction</h2>
  <?php txn_form($d, $err, 'Update'); ?>
</div>
<?php render_footer(); ?>
