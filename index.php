<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

header('Location: ' . (isLoggedIn() ? 'dashboard.php' : 'login.php'));
exit;
