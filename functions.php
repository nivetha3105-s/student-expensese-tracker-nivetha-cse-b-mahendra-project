<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Must match the category ENUM in schema.sql
const CATEGORIES = ['Food', 'Travel', 'Books', 'Hostel', 'Entertainment', 'Education', 'Other'];

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function inr($n): string {
    return '₹' . number_format((float)$n, 2);
}

function redirect(string $path): void {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

// Call at the top of protected pages: $uid = require_login();
function require_login(): int {
    if (!is_logged_in()) {
        flash('error', 'Please login first.');
        redirect('/login.php');
    }
    return (int)$_SESSION['user_id'];
}

function flash(string $type, string $msg): void {
    $_SESSION['flash'][] = [$type, $msg];
}

/** Reads and validates the add/edit form. Returns [data, errors]. */
function read_txn(): array {
    $d = [
        'type'        => $_POST['type'] ?? '',
        'category'    => $_POST['category'] ?? '',
        'amount'      => trim($_POST['amount'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'txn_date'    => $_POST['txn_date'] ?? '',
    ];
    $err = [];
    if (!in_array($d['type'], ['income', 'expense'], true)) $err[] = 'Choose income or expense.';
    if (!in_array($d['category'], CATEGORIES, true))        $err[] = 'Choose a valid category.';
    if (!is_numeric($d['amount']) || (float)$d['amount'] <= 0 || (float)$d['amount'] > 99999999.99)
        $err[] = 'Amount must be a positive number.';
    if (mb_strlen($d['description']) > 200)                 $err[] = 'Description is too long (max 200 characters).';
    $dt = DateTime::createFromFormat('Y-m-d', $d['txn_date']);
    if (!$dt || $dt->format('Y-m-d') !== $d['txn_date'])    $err[] = 'Enter a valid date.';
    return [$d, $err];
}

/** Renders the add/edit form. Posts back to the same URL so ?id= / ?type= are kept. */
function txn_form(array $d, array $err, string $btn): void {
    foreach ($err as $x) echo '<div class="alert error">' . e($x) . '</div>';
    ?>
<form method="post" class="js-validate" novalidate>
  <label>Type
    <select name="type">
      <option value="expense" <?= ($d['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense</option>
      <option value="income"  <?= ($d['type'] ?? '') === 'income'  ? 'selected' : '' ?>>Income</option>
    </select>
  </label>
  <label>Category
    <select name="category">
      <?php foreach (CATEGORIES as $c): ?>
        <option <?= ($d['category'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Amount (₹)<input type="number" name="amount" step="0.01" min="0.01" value="<?= e($d['amount'] ?? '') ?>" required></label>
  <label>Description<input type="text" name="description" maxlength="200" value="<?= e($d['description'] ?? '') ?>"></label>
  <label>Date<input type="date" name="txn_date" value="<?= e($d['txn_date'] ?? '') ?>" required></label>
  <button class="btn" type="submit"><?= e($btn) ?></button>
  <a class="btn ghost" href="<?= BASE_URL ?>/dashboard.php">Cancel</a>
</form>
<?php
}

function render_header(string $title = ''): void {
    $b = BASE_URL;
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . e($title) . ' - Student Expense Tracker</title>'
       . '<link rel="stylesheet" href="' . $b . '/css/style.css"></head><body>';
    echo '<header class="topbar"><a class="brand" href="' . $b . '/index.php">Expense Tracker</a><nav>';
    if (is_logged_in()) {
        echo '<a href="' . $b . '/dashboard.php">Dashboard</a>'
           . '<a href="' . $b . '/add_transaction.php">Add transaction</a>'
           . '<a href="' . $b . '/report.php">Report</a>'
           . '<a href="' . $b . '/logout.php">Logout (' . e($_SESSION['name'] ?? '') . ')</a>';
    } else {
        echo '<a href="' . $b . '/login.php">Login</a><a href="' . $b . '/login.php?mode=register">Register</a>';
    }
    echo '</nav></header><main class="container">';
    foreach ($_SESSION['flash'] ?? [] as [$type, $msg]) {
        echo '<div class="alert ' . e($type) . '">' . e($msg) . '</div>';
    }
    unset($_SESSION['flash']);
}

function render_footer(): void {
    echo '</main><footer class="footer">Student Expense Tracker &middot; Web Technology project</footer>'
       . '<script src="' . BASE_URL . '/js/app.js"></script></body></html>';
}
