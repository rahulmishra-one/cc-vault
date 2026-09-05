<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$user = currentUser();
if (!$user) {
    destroySession();
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmation = $_POST['confirm_password'] ?? '';

    if (!verifyCsrfToken()) {
        $error = 'Your session has expired. Please try again.';
    } elseif (!is_string($currentPassword) || !is_string($newPassword) || !is_string($confirmation)) {
        $error = 'Please enter your passwords again.';
    } elseif (strlen($newPassword) < 12) {
        $error = 'Your new password must contain at least 12 characters.';
    } elseif ($newPassword !== $confirmation) {
        $error = 'Your new passwords do not match.';
    } else {
        $statement = db()->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $user['id']]);
        $account = $statement->fetch();

        if (!$account || !password_verify($currentPassword, $account['password_hash'])) {
            $error = 'Your current password is incorrect.';
        } elseif (password_verify($newPassword, $account['password_hash'])) {
            $error = 'Choose a new password that is different from your current one.';
        } else {
            $update = db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
            $update->execute([
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'id' => $user['id'],
            ]);
            session_regenerate_id(true);
            $_SESSION['last_activity'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $success = 'Your password has been changed successfully.';
        }
    }
}

$pageTitle = 'Change password';
$activePage = 'password';
require __DIR__ . '/includes/header.php';
?>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="content">
        <header class="topbar">
            <button class="menu-button" type="button" aria-label="Open navigation" aria-controls="sidebar">☰</button>
            <div><p class="eyebrow">SECURITY</p><h1>Change password</h1></div>
            <div class="avatar" title="<?= escape($user['full_name']) ?>"><?= escape(strtoupper(substr($user['full_name'], 0, 1))) ?></div>
        </header>
        <section class="settings-card">
            <h2>Protect your account</h2>
            <p class="muted">Use a unique password of at least 12 characters.</p>
            <?php if ($error): ?><p class="alert" role="alert"><?= escape($error) ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success" role="status"><?= escape($success) ?></p><?php endif; ?>
            <form method="post" class="login-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                <label>Current password<input type="password" name="current_password" required autocomplete="current-password"></label>
                <label>New password<input type="password" name="new_password" required minlength="12" autocomplete="new-password"></label>
                <p class="password-rules">At least 12 characters. Use a memorable, unique phrase.</p>
                <label>Confirm new password<input type="password" name="confirm_password" required minlength="12" autocomplete="new-password"></label>
                <button type="submit">Update password</button>
            </form>
        </section>
    </main>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
