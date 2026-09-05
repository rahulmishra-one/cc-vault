<aside class="sidebar" id="sidebar">
    <a class="brand" href="dashboard.php"><span class="brand-mark">C</span> CC Vault</a>
    <nav aria-label="Main navigation">
        <a class="nav-link <?= ($activePage ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Overview</a>
        <a class="nav-link" href="#" aria-disabled="true">My Cards <span>Soon</span></a>
        <a class="nav-link" href="#" aria-disabled="true">Merchants <span>Soon</span></a>
        <a class="nav-link" href="#" aria-disabled="true">Search <span>Soon</span></a>
        <a class="nav-link" href="#" aria-disabled="true">Recommendations <span>Soon</span></a>
    </nav>
    <div class="sidebar-footer">
        <a class="nav-link <?= ($activePage ?? '') === 'password' ? 'active' : '' ?>" href="change-password.php">Change password</a>
        <form method="post" action="logout.php" class="signout-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
            <button class="nav-link signout-button" type="submit">Sign out</button>
        </form>
    </div>
</aside>
