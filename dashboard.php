<?php
require_once 'config/database.php';
$uid = require_login();

$month = preg_match('/^\d{4}-\d{2}$/', $_GET['month'] ?? '') ? $_GET['month'] : date('Y-m');
$q   = trim($_GET['q'] ?? '');
$cat = in_array($_GET['category'] ?? '', CATEGORIES) ? $_GET['category'] : '';
$start = $month . '-01';
$end   = date('Y-m-t', strtotime($start));

// 1) Monthly summary: SUM with CASE
$s = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN type='income'  THEN amount END),0) AS inc,
                           COALESCE(SUM(CASE WHEN type='expense' THEN amount END),0) AS exp
                    FROM transactions WHERE user_id = ? AND txn_date BETWEEN ? AND ?");
$s->execute([$uid, $start, $end]);
$tot = $s->fetch();
$balance = $tot['inc'] - $tot['exp'];

// 2) Expenses by category: GROUP BY
$s = $pdo->prepare("SELECT category, SUM(amount) AS total FROM transactions
                    WHERE user_id = ? AND type = 'expense' AND txn_date BETWEEN ? AND ?
                    GROUP BY category ORDER BY total DESC");
$s->execute([$uid, $start, $end]);
$byCat = $s->fetchAll();

// 3) Transaction list with search + category filter
$sql = 'SELECT * FROM transactions WHERE user_id = ? AND txn_date BETWEEN ? AND ?';
$p = [$uid, $start, $end];
if ($q !== '')   { $sql .= ' AND (description LIKE ? OR category LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
if ($cat !== '') { $sql .= ' AND category = ?'; $p[] = $cat; }
$s = $pdo->prepare($sql . ' ORDER BY txn_date DESC, id DESC');
$s->execute($p);
$rows = $s->fetchAll();

render_header('Dashboard');
?>
<h2>Student Expense Dashboard</h2>
<form method="get" class="search-bar">
  <input type="month" name="month" value="<?= e($month) ?>" data-auto>
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search description or category">
  <select name="category" data-auto>
    <option value="">All categories</option>
    <?php foreach (CATEGORIES as $c): ?><option <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Search</button>
</form>

<div class="grid stats">
  <div class="card"><p class="muted">Income</p><h3 class="inc"><?= inr($tot['inc']) ?></h3></div>
  <div class="card"><p class="muted">Expenses</p><h3 class="exp"><?= inr($tot['exp']) ?></h3></div>
  <div class="card"><p class="muted">Balance</p><h3 class="<?= $balance < 0 ? 'exp' : 'inc' ?>"><?= inr($balance) ?></h3></div>
</div>

<div class="card"><h3>Spending by category</h3>
  <?php if (!$byCat): ?><p class="muted">No expenses this month.</p><?php endif; ?>
  <?php foreach ($byCat as $r): $pct = $tot['exp'] > 0 ? round($r['total'] / $tot['exp'] * 100) : 0; ?>
    <div class="bar-row"><span><?= e($r['category']) ?></span>
      <div class="bar"><i style="width:<?= $pct ?>%"></i></div>
      <b><?= inr($r['total']) ?></b></div>
  <?php endforeach; ?>
</div>

<p><a class="btn" href="add_transaction.php?type=income">+ Add income</a>
   <a class="btn danger" href="add_transaction.php?type=expense">+ Add expense</a></p>

<h3>Transactions (<?= count($rows) ?>)</h3>
<?php if (!$rows): ?><p class="muted">No transactions found.</p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Date</th><th>Category</th><th>Description</th><th class="r">Amount</th><th></th></tr>
  <?php foreach ($rows as $t): ?>
  <tr>
    <td><?= e(date('d M', strtotime($t['txn_date']))) ?></td>
    <td><?= e($t['category']) ?></td>
    <td><?= e($t['description']) ?></td>
    <td class="r <?= $t['type'] === 'income' ? 'inc' : 'exp' ?>"><?= $t['type'] === 'income' ? '+' : '-' ?><?= inr($t['amount']) ?></td>
    <td class="act">
      <a class="btn small ghost" href="edit_transaction.php?id=<?= (int)$t['id'] ?>">Edit</a>
      <form method="post" action="delete_transaction.php" data-confirm="Delete this transaction?">
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <input type="hidden" name="month" value="<?= e($month) ?>">
        <button class="btn small danger" type="submit">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php render_footer(); ?>
