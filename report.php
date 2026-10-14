<?php
require_once 'config/database.php';
$uid = require_login();

// Monthly summary across all months: GROUP BY month
$s = $pdo->prepare("SELECT DATE_FORMAT(txn_date, '%Y-%m') AS ym,
                           SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS inc,
                           SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS exp
                    FROM transactions WHERE user_id = ?
                    GROUP BY ym ORDER BY ym DESC");
$s->execute([$uid]);
$months = $s->fetchAll();

// All-time expenses per category
$s = $pdo->prepare("SELECT category, SUM(amount) AS total, COUNT(*) AS n FROM transactions
                    WHERE user_id = ? AND type = 'expense' GROUP BY category ORDER BY total DESC");
$s->execute([$uid]);
$cats = $s->fetchAll();

render_header('Report');
?>
<h2>Monthly report</h2>
<?php if (!$months): ?><p class="muted">No data yet. <a href="add_transaction.php">Add a transaction</a>.</p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Month</th><th class="r">Income</th><th class="r">Expenses</th><th class="r">Balance</th></tr>
  <?php foreach ($months as $m): $bal = $m['inc'] - $m['exp']; ?>
  <tr>
    <td><a href="dashboard.php?month=<?= e($m['ym']) ?>"><?= e(date('F Y', strtotime($m['ym'] . '-01'))) ?></a></td>
    <td class="r inc"><?= inr($m['inc']) ?></td>
    <td class="r exp"><?= inr($m['exp']) ?></td>
    <td class="r <?= $bal < 0 ? 'exp' : 'inc' ?>"><?= inr($bal) ?></td>
  </tr>
  <?php endforeach; ?>
</table></div>

<h3>All-time spending by category</h3>
<div class="table-wrap"><table>
  <tr><th>Category</th><th class="r">Transactions</th><th class="r">Total</th></tr>
  <?php foreach ($cats as $c): ?>
  <tr><td><?= e($c['category']) ?></td><td class="r"><?= (int)$c['n'] ?></td><td class="r"><?= inr($c['total']) ?></td></tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php render_footer(); ?>
