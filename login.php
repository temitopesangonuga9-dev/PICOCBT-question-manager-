<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdmin()) redirect('dashboard.php');

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid form submission, please try again.';
    } else {
        $result = attemptLogin($pdo, trim($_POST['email'] ?? ''), $_POST['password'] ?? '', 'admin');
        if ($result['success']) {
            redirect('dashboard.php');
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Admin Login';
$assetPath = '../assets/';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card card-narrow">
    <div class="card-header">Admin Login</div>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <label>Email</label>
            <input type="email" name="email" required autofocus>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn btn-block">Login</button>
        </form>
        <div class="helper-links">
            <a href="../teacher/login.php">Teacher Login</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
