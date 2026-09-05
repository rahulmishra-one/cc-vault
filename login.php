<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Your session has expired. Please try again.';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $statement = db()->prepare('SELECT id, password_hash FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
        $statement->execute(['email' => $email ?: '']);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['last_activity'] = time();
            header('Location: dashboard.php');
            exit;
        }
        usleep(250000);
        $error = 'Please check your email address and password.';
    }
}

$pageTitle = 'Sign in';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-shell">
    <section class="auth-panel">
        <a class="brand auth-brand" href="index.php"><span class="brand-mark">C</span> CC Vault</a>
        <p class="eyebrow">YOUR CARD INTELLIGENCE HUB</p>
        <h1>Welcome back</h1>
        <p class="muted">Sign in to manage your cards and unlock smarter recommendations.</p>
        <?php if ($error): ?><p class="alert" role="alert"><?= escape($error) ?></p><?php endif; ?>
        <form method="post" class="login-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
            <label>Email<input type="email" name="email" required autocomplete="email" placeholder="you@example.com"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password" placeholder="••••••••"></label>
            <button type="submit">Sign in</button>
        </form>
        <p class="login-help">First launch? Use the development credentials listed in the README.</p>
    </section>
    <aside class="auth-aside"><p>Spend smarter.<br><strong>Get more from every card.</strong></p></aside>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
