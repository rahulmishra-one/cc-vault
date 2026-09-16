<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$user = requireCurrentUser();
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Your session has expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'archive') {
        $cardId = filter_input(INPUT_POST, 'card_id', FILTER_VALIDATE_INT);
        if (!$cardId) {
            $error = 'That card could not be found.';
        } else {
            $archive = db()->prepare("UPDATE cards SET status = 'archived' WHERE id = :id AND user_id = :user_id AND status != 'archived'");
            $archive->execute(['id' => $cardId, 'user_id' => $user['id']]);
            $notice = $archive->rowCount() === 1 ? 'Card archived.' : 'That card is already archived or unavailable.';
        }
    }
}

$cardsStatement = db()->prepare("SELECT id, card_name, issuer, network, last_four, annual_fee, billing_day, renewal_month, renewal_year, status FROM cards WHERE user_id = :user_id ORDER BY CASE WHEN status = 'active' THEN 0 ELSE 1 END, created_at DESC");
$cardsStatement->execute(['user_id' => $user['id']]);
$cards = $cardsStatement->fetchAll();

$pageTitle = 'My cards';
$activePage = 'cards';
require __DIR__ . '/includes/header.php';
?>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="content">
        <header class="topbar">
            <button class="menu-button" type="button" aria-label="Open navigation" aria-controls="sidebar">☰</button>
            <div><p class="eyebrow">CARD MANAGEMENT</p><h1>My cards</h1></div>
            <a class="primary-link" href="card.php">Add a card</a>
        </header>
        <?php if ($notice): ?><p class="success" role="status"><?= escape($notice) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="alert" role="alert"><?= escape($error) ?></p><?php endif; ?>
        <?php if ($cards): ?>
            <section class="cards-list" aria-label="Your cards">
                <?php foreach ($cards as $card): ?>
                    <article class="card-row <?= $card['status'] !== 'active' ? 'is-archived' : '' ?>">
                        <div class="card-row-mark"><?= escape(substr($card['issuer'], 0, 1)) ?></div>
                        <div class="card-row-main">
                            <h2><?= escape($card['card_name']) ?></h2>
                            <p><?= escape($card['issuer']) ?> · <?= escape($card['network']) ?> · •••• <?= escape($card['last_four']) ?></p>
                        </div>
                        <div class="card-row-meta"><span>Annual fee</span><strong>₹<?= number_format((float) $card['annual_fee'], 0) ?></strong></div>
                        <div class="card-row-meta"><span>Renewal</span><strong><?= $card['renewal_month'] && $card['renewal_year'] ? escape(date('M', mktime(0, 0, 0, (int) $card['renewal_month'], 1)) . ' ' . $card['renewal_year']) : 'Not set' ?></strong></div>
                        <div class="card-row-actions">
                            <a class="secondary-link" href="card.php?id=<?= (int) $card['id'] ?>">Edit</a>
                            <?php if ($card['status'] === 'active'): ?>
                                <form method="post" data-confirm="Archive this card? You can keep its history, but it will no longer appear as active.">
                                    <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="archive">
                                    <input type="hidden" name="card_id" value="<?= (int) $card['id'] ?>">
                                    <button class="text-button" type="submit">Archive</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <section class="empty-panel"><span>▣</span><h2>Add your first card</h2><p>Store only safe card details such as the last four digits, fees, billing day, and benefits.</p><a class="primary-link" href="card.php">Add a card</a></section>
        <?php endif; ?>
    </main>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
