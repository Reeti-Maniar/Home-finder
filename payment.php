<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Payment';
$extraScripts = [asset('/js/payment.js')];
requireClientLogin();
$pdo = getDBConnection();
$propertyId = (int) ($_GET['property_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM properties WHERE property_id = ? AND status = "active"');
$stmt->execute([$propertyId]);
$property = $stmt->fetch();
if (!$property) { redirectWithMessage('/properties.php', 'error', 'Property not found.'); }
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container payment-layout">
        <div class="panel">
            <div class="progress-wrap"><progress max="3" value="2"></progress><div class="progress-labels"><span>Property Selected</span><span>Confirm & Pay</span><span>Meeting Scheduled</span></div></div>
            <div class="booking-summary">
                <img src="<?= htmlspecialchars(firstImageForProperty($pdo, $property)) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                <div><h1><?= htmlspecialchars($property['title']) ?></h1><p><?= htmlspecialchars($property['area_locality']) ?> • <?= htmlspecialchars($property['bhk_type']) ?></p><strong><?= moneyFormat((float) $property['price']) ?></strong></div>
            </div>
            <p><strong>Meeting Arrangement Fee:</strong> <?= moneyFormat(499) ?></p>
            <form action="<?= url('/php/interests.php?action=confirm') ?>" method="post" class="form-grid" data-payment-form>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="property_id" value="<?= $propertyId ?>">
                <label>Name on Card <input class="form-input" type="text" name="card_name" required></label>
                <label>Card Number <input class="form-input" type="text" name="card_number" required pattern="\d{16}"></label>
                <div class="two-col"><label>Expiry <input class="form-input" type="text" name="card_expiry" required placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/\d{2}"></label><label>CVV <input class="form-input" type="password" name="card_cvv" required pattern="\d{3}"></label></div>
                <p class="muted">This is a simulated payment for demonstration purposes. No real transaction is processed.</p>
                <button class="btn btn-primary" type="submit">Confirm & Pay</button>
            </form>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
