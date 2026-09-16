<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$user = requireCurrentUser();
$networks = ['Visa', 'Mastercard', 'RuPay', 'Amex', 'Other'];
$benefitTypes = ['Cashback', 'Travel', 'Dining', 'Lounge', 'Insurance', 'Milestone', 'Other'];
$cardId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$card = [
    'id' => null, 'card_name' => '', 'issuer' => '', 'network' => 'Visa', 'last_four' => '', 'annual_fee' => '0.00',
    'billing_day' => '', 'renewal_month' => '', 'renewal_year' => '', 'notes' => '', 'status' => 'active',
];
$error = '';
$notice = '';

function loadOwnedCard(PDO $database, int $cardId, int $userId): ?array
{
    $statement = $database->prepare('SELECT * FROM cards WHERE id = :id AND user_id = :user_id LIMIT 1');
    $statement->execute(['id' => $cardId, 'user_id' => $userId]);
    return $statement->fetch() ?: null;
}

if ($cardId !== null) {
    $loaded = loadOwnedCard(db(), $cardId, (int) $user['id']);
    if ($loaded === null) {
        http_response_code(404);
        $pageTitle = 'Card not found';
        require __DIR__ . '/includes/header.php';
        echo '<main class="simple-message"><h1>Card not found</h1><p>This card is unavailable.</p><a class="primary-link" href="cards.php">Back to my cards</a></main>';
        require __DIR__ . '/includes/footer.php';
        exit;
    }
    $card = $loaded;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!verifyCsrfToken()) {
        $error = 'Your session has expired. Please try again.';
    } elseif ($action === 'save_card') {
        $card['card_name'] = trim((string) ($_POST['card_name'] ?? ''));
        $card['issuer'] = trim((string) ($_POST['issuer'] ?? ''));
        $card['network'] = (string) ($_POST['network'] ?? '');
        $card['last_four'] = preg_replace('/\D/', '', (string) ($_POST['last_four'] ?? ''));
        $card['annual_fee'] = trim((string) ($_POST['annual_fee'] ?? '0'));
        $card['billing_day'] = trim((string) ($_POST['billing_day'] ?? ''));
        $card['renewal_month'] = trim((string) ($_POST['renewal_month'] ?? ''));
        $card['renewal_year'] = trim((string) ($_POST['renewal_year'] ?? ''));
        $card['notes'] = trim((string) ($_POST['notes'] ?? ''));

        if (strlen($card['card_name']) < 2 || strlen($card['card_name']) > 160) {
            $error = 'Enter a card name between 2 and 160 characters.';
        } elseif (strlen($card['issuer']) < 2 || strlen($card['issuer']) > 120) {
            $error = 'Enter the card issuer.';
        } elseif (!in_array($card['network'], $networks, true)) {
            $error = 'Choose a valid card network.';
        } elseif (!preg_match('/^\d{4}$/', $card['last_four'])) {
            $error = 'Enter exactly four digits from the card number.';
        } elseif (!is_numeric($card['annual_fee']) || (float) $card['annual_fee'] < 0 || (float) $card['annual_fee'] > 999999) {
            $error = 'Enter a valid annual fee.';
        } elseif ($card['billing_day'] !== '' && (!ctype_digit($card['billing_day']) || (int) $card['billing_day'] < 1 || (int) $card['billing_day'] > 31)) {
            $error = 'Billing day must be between 1 and 31.';
        } elseif (($card['renewal_month'] === '') !== ($card['renewal_year'] === '')) {
            $error = 'Enter both a renewal month and year, or leave both blank.';
        } elseif ($card['renewal_month'] !== '' && (!ctype_digit($card['renewal_month']) || (int) $card['renewal_month'] < 1 || (int) $card['renewal_month'] > 12 || !ctype_digit($card['renewal_year']) || (int) $card['renewal_year'] < (int) date('Y') || (int) $card['renewal_year'] > (int) date('Y') + 25)) {
            $error = 'Enter a valid future renewal date.';
        } elseif (strlen($card['notes']) > 3000) {
            $error = 'Notes cannot exceed 3,000 characters.';
        } else {
            $payload = [
                'card_name' => $card['card_name'], 'issuer' => $card['issuer'], 'network' => $card['network'], 'last_four' => $card['last_four'],
                'annual_fee' => number_format((float) $card['annual_fee'], 2, '.', ''), 'billing_day' => $card['billing_day'] === '' ? null : (int) $card['billing_day'],
                'renewal_month' => $card['renewal_month'] === '' ? null : (int) $card['renewal_month'], 'renewal_year' => $card['renewal_year'] === '' ? null : (int) $card['renewal_year'],
                'notes' => $card['notes'] === '' ? null : $card['notes'],
            ];
            if ($cardId !== null) {
                $payload['id'] = $cardId;
                $payload['user_id'] = $user['id'];
                $update = db()->prepare('UPDATE cards SET card_name = :card_name, issuer = :issuer, network = :network, last_four = :last_four, annual_fee = :annual_fee, billing_day = :billing_day, renewal_month = :renewal_month, renewal_year = :renewal_year, notes = :notes WHERE id = :id AND user_id = :user_id');
                $update->execute($payload);
                header('Location: card.php?id=' . $cardId . '&saved=1');
                exit;
            }
            $payload['user_id'] = $user['id'];
            $insert = db()->prepare('INSERT INTO cards (user_id, card_name, issuer, network, last_four, annual_fee, billing_day, renewal_month, renewal_year, notes) VALUES (:user_id, :card_name, :issuer, :network, :last_four, :annual_fee, :billing_day, :renewal_month, :renewal_year, :notes)');
            $insert->execute($payload);
            header('Location: card.php?id=' . (int) db()->lastInsertId() . '&created=1');
            exit;
        }
    } elseif ($cardId !== null && $action === 'add_benefit') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $type = (string) ($_POST['benefit_type'] ?? '');
        $description = trim((string) ($_POST['description'] ?? ''));
        $value = trim((string) ($_POST['value_amount'] ?? ''));
        if (!in_array($type, $benefitTypes, true) || strlen($title) < 2 || strlen($title) > 160) {
            $error = 'Provide a title and valid benefit type.';
        } elseif (strlen($description) > 3000 || ($value !== '' && (!is_numeric($value) || (float) $value < 0))) {
            $error = 'Check the benefit value and description.';
        } else {
            $addBenefit = db()->prepare('INSERT INTO card_benefits (card_id, benefit_type, title, description, value_amount) VALUES (:card_id, :benefit_type, :title, :description, :value_amount)');
            $addBenefit->execute(['card_id' => $cardId, 'benefit_type' => $type, 'title' => $title, 'description' => $description === '' ? null : $description, 'value_amount' => $value === '' ? null : number_format((float) $value, 2, '.', '')]);
            header('Location: card.php?id=' . $cardId . '&benefit=1');
            exit;
        }
    } elseif ($cardId !== null && $action === 'delete_benefit') {
        $benefitId = filter_input(INPUT_POST, 'benefit_id', FILTER_VALIDATE_INT);
        if (!$benefitId) {
            $error = 'That benefit could not be found.';
        } else {
            $ownedBenefit = db()->prepare('SELECT b.id FROM card_benefits b INNER JOIN cards c ON c.id = b.card_id WHERE b.id = :benefit_id AND c.id = :card_id AND c.user_id = :user_id');
            $ownedBenefit->execute(['benefit_id' => $benefitId, 'card_id' => $cardId, 'user_id' => $user['id']]);
            if ($ownedBenefit->fetch()) {
                $delete = db()->prepare('DELETE FROM card_benefits WHERE id = :id');
                $delete->execute(['id' => $benefitId]);
                header('Location: card.php?id=' . $cardId . '&benefit_deleted=1');
                exit;
            }
            $error = 'That benefit is unavailable.';
        }
    }
}

if (isset($_GET['saved'])) { $notice = 'Card details saved.'; }
if (isset($_GET['created'])) { $notice = 'Card added. Add its benefits below when you are ready.'; }
if (isset($_GET['benefit'])) { $notice = 'Benefit added.'; }
if (isset($_GET['benefit_deleted'])) { $notice = 'Benefit removed.'; }
$benefits = [];
if ($cardId !== null) {
    $benefitQuery = db()->prepare('SELECT id, benefit_type, title, description, value_amount FROM card_benefits WHERE card_id = :card_id ORDER BY created_at DESC');
    $benefitQuery->execute(['card_id' => $cardId]);
    $benefits = $benefitQuery->fetchAll();
}

$pageTitle = $cardId === null ? 'Add a card' : 'Edit card';
$activePage = 'cards';
require __DIR__ . '/includes/header.php';
?>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="content">
        <header class="topbar">
            <button class="menu-button" type="button" aria-label="Open navigation" aria-controls="sidebar">☰</button>
            <div><p class="eyebrow">CARD MANAGEMENT</p><h1><?= $cardId === null ? 'Add a card' : 'Edit card' ?></h1></div>
            <a class="secondary-link" href="cards.php">Back to cards</a>
        </header>
        <?php if ($notice): ?><p class="success" role="status"><?= escape($notice) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="alert" role="alert"><?= escape($error) ?></p><?php endif; ?>
        <section class="form-card">
            <p class="form-note">For your security, never store a complete card number, CVV, PIN, expiry date, or OTP in CC Vault.</p>
            <form method="post" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="action" value="save_card">
                <div class="form-grid"><label>Card name<input name="card_name" required maxlength="160" value="<?= escape($card['card_name']) ?>" placeholder="e.g. Regalia Gold"></label><label>Issuer<input name="issuer" required maxlength="120" value="<?= escape($card['issuer']) ?>" placeholder="e.g. HDFC Bank"></label></div>
                <div class="form-grid"><label>Network<select name="network"><?php foreach ($networks as $network): ?><option value="<?= escape($network) ?>" <?= $card['network'] === $network ? 'selected' : '' ?>><?= escape($network) ?></option><?php endforeach; ?></select></label><label>Last four digits<input name="last_four" required inputmode="numeric" pattern="[0-9]{4}" maxlength="4" value="<?= escape($card['last_four']) ?>" placeholder="1234"></label></div>
                <div class="form-grid"><label>Annual fee (₹)<input name="annual_fee" type="number" min="0" max="999999" step="0.01" required value="<?= escape((string) $card['annual_fee']) ?>"></label><label>Billing day <span class="label-optional">Optional</span><input name="billing_day" type="number" min="1" max="31" value="<?= escape((string) $card['billing_day']) ?>" placeholder="1–31"></label></div>
                <div class="form-grid"><label>Renewal month <span class="label-optional">Optional</span><select name="renewal_month"><option value="">Not set</option><?php for ($month = 1; $month <= 12; $month++): ?><option value="<?= $month ?>" <?= (int) $card['renewal_month'] === $month ? 'selected' : '' ?>><?= escape(date('F', mktime(0, 0, 0, $month, 1))) ?></option><?php endfor; ?></select></label><label>Renewal year <span class="label-optional">Optional</span><select name="renewal_year"><option value="">Not set</option><?php for ($year = (int) date('Y'); $year <= (int) date('Y') + 25; $year++): ?><option value="<?= $year ?>" <?= (int) $card['renewal_year'] === $year ? 'selected' : '' ?>><?= $year ?></option><?php endfor; ?></select></label></div>
                <label>Private notes <span class="label-optional">Optional</span><textarea name="notes" maxlength="3000" rows="4" placeholder="Keep only safe reminders or details."><?= escape($card['notes']) ?></textarea></label>
                <button type="submit"><?= $cardId === null ? 'Add card' : 'Save changes' ?></button>
            </form>
        </section>
        <?php if ($cardId !== null): ?>
            <section class="benefits-section"><div class="section-heading"><div><p class="eyebrow">CARD BENEFITS</p><h2>Benefits and offers</h2></div></div>
                <?php if ($benefits): ?><div class="benefit-list"><?php foreach ($benefits as $benefit): ?><article class="benefit-item"><div><strong><?= escape($benefit['title']) ?></strong><p><?= escape($benefit['benefit_type']) ?><?= $benefit['value_amount'] !== null ? ' · ₹' . number_format((float) $benefit['value_amount'], 0) : '' ?></p><?php if ($benefit['description']): ?><small><?= escape($benefit['description']) ?></small><?php endif; ?></div><form method="post" data-confirm="Remove this benefit?"><input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="action" value="delete_benefit"><input type="hidden" name="benefit_id" value="<?= (int) $benefit['id'] ?>"><button class="text-button" type="submit">Remove</button></form></article><?php endforeach; ?></div><?php endif; ?>
                <form method="post" class="benefit-form"><input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="action" value="add_benefit"><div class="form-grid"><label>Benefit title<input name="title" maxlength="160" required placeholder="e.g. Airport lounge access"></label><label>Type<select name="benefit_type"><?php foreach ($benefitTypes as $type): ?><option value="<?= escape($type) ?>"><?= escape($type) ?></option><?php endforeach; ?></select></label></div><div class="form-grid"><label>Estimated value (₹) <span class="label-optional">Optional</span><input name="value_amount" type="number" min="0" step="0.01"></label><label>Description <span class="label-optional">Optional</span><input name="description" maxlength="3000" placeholder="Terms, limits, or reminder"></label></div><button type="submit">Add benefit</button></form>
            </section>
        <?php endif; ?>
    </main>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
