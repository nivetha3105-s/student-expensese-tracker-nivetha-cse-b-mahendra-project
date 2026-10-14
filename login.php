<?php
require_once 'config/database.php';
if (is_logged_in()) redirect('/dashboard.php');

$mode = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';
$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if ($mode === 'register') {
        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
        if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
        if (!$errors) {
            $c = $pdo->prepare('SELECT id FROM expense_users WHERE email = ?');
            $c->execute([$email]);
            if ($c->fetch()) $errors[] = 'This email is already registered.';
        }
        if (!$errors) {
            $pdo->prepare('INSERT INTO expense_users (name, email, password) VALUES (?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            flash('success', 'Account created. Please login.');
            redirect('/login.php');
        }
    } else {
        $s = $pdo->prepare('SELECT * FROM expense_users WHERE email = ?');
        $s->execute([$email]);
        $u = $s->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['name'] = $u['name'];
            redirect('/dashboard.php');
        }
        $errors[] = 'Wrong email or password.';
    }
}
render_header($mode === 'register' ? 'Register' : 'Login');
?>
<div class="form-box">
  <h2><?= $mode === 'register' ? 'Create account' : 'Login' ?></h2>
  <?php foreach ($errors as $x): ?><div class="alert error"><?= e($x) ?></div><?php endforeach; ?>
  <form method="post" class="js-validate" novalidate>
    <?php if ($mode === 'register'): ?><label>Full name<input type="text" name="name" value="<?= e($name) ?>" required></label><?php endif; ?>
    <label>Email<input type="email" name="email" value="<?= e($email) ?>" required></label>
    <label>Password<input type="password" name="password" <?= $mode === 'register' ? 'minlength="6" ' : '' ?>required></label>
    <button class="btn" type="submit"><?= $mode === 'register' ? 'Register' : 'Login' ?></button>
  </form>
  <p class="muted"><?= $mode === 'register' ? '<a href="login.php">Back to login</a>' : '<a href="login.php?mode=register">Create an account</a>' ?></p>
</div>
<?php render_footer(); ?>
