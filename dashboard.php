<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$user = currentUser();
$cards = db()->query('SELECT card_name, issuer, network, last_four, annual_fee, status FROM cards WHERE status = "active" ORDER BY created_at DESC LIMIT 4')->fetchAll();
$summary = db()->query('SELECT COUNT(*) AS card_count, COALESCE(SUM(annual_fee), 0) AS annual_fees FROM cards WHERE status = "active"')->fetch();
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="content">
        <header class="topbar">
            <button class="menu-button" type="button" aria-label="Open navigation" aria-controls="sidebar">☰</button>
            <div><p class="eyebrow">DASHBOARD</p><h1>Good to see you, <?= escape(explode(' ', $user['full_name'])[0] ?? 'there') ?>.</h1></div>
            <div class="avatar" title="<?= escape($user['full_name']) ?>"><?= escape(strtoupper(substr($user['full_name'], 0, 1))) ?></div>
        </header>
        <section class="metrics" aria-label="Account overview">
            <article><p>Active cards</p><strong><?= (int) $summary['card_count'] ?></strong><span>Cards in your wallet</span></article>
            <article><p>Annual fees</p><strong>₹<?= number_format((float) $summary['annual_fees'], 0) ?></strong><span>Across active cards</span></article>
            <article><p>Potential value</p><strong>Coming soon</strong><span>Recommendation engine</span></article>
        </section>
        <section class="section-heading"><div><p class="eyebrow">YOUR WALLET</p><h2>My cards</h2></div><a class="text-link" href="#">Manage cards →</a></section>
        <section class="card-grid">
            <?php foreach ($cards as $card): ?>
                <article class="credit-card">
                    <div class="card-network"><?= escape($card['network']) ?></div>
                    <div class="card-chip">▣</div>
                    <p class="card-number">•••• &nbsp;•••• &nbsp;•••• &nbsp;<?= escape($card['last_four']) ?></p>
                    <div class="card-details"><span><?= escape($card['card_name']) ?><small><?= escape($card['issuer']) ?></small></span><strong><?= escape($card['network']) ?></strong></div>
                </article>
            <?php endforeach; ?>
            <?php if (!$cards): ?><p class="empty-state">No cards yet. The Cards module will let you add one.</p><?php endif; ?>
        </section>
        <section class="coming-soon"><span>✦</span><div><h2>Your personalized card recommendations are on their way.</h2><p>Once you add your spending profile, CC Vault will identify the best card for every purchase.</p></div></section>
    </main>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
