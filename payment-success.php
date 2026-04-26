<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Payment Success';
requireClientLogin();
$pdo = getDBConnection();
$interestId = (int) ($_GET['interest_id'] ?? 0);
$stmt = $pdo->prepare('SELECT i.*, p.title, p.area_locality, p.price, pay.amount, pay.paid_at, u.phone FROM interests i INNER JOIN properties p ON p.property_id = i.property_id INNER JOIN users u ON u.user_id = i.user_id LEFT JOIN payments pay ON pay.interest_id = i.interest_id WHERE i.interest_id = ? AND i.user_id = ?');
$stmt->execute([$interestId, $_SESSION['user_id']]);
$record = $stmt->fetch();
if (!$record) { redirectWithMessage('/dashboard.php', 'error', 'Interest record not found.'); }
require_once __DIR__ . '/includes/header.php';
?>
<section class="section success-section">
    <div class="container panel success-card">
        <div class="success-mark">?</div>
        <h1>You're All Set!</h1>
        <p>Your interest in <?= htmlspecialchars($record['title']) ?> has been confirmed. Our team will contact you within 24-48 hours on <?= htmlspecialchars((string) $record['phone']) ?> to schedule a site visit or call.</p>
        <div class="detail-grid">
            <div><strong>Property</strong><span><?= htmlspecialchars($record['title']) ?></span></div>
            <div><strong>Area</strong><span><?= htmlspecialchars($record['area_locality']) ?></span></div>
            <div><strong>Fee Paid</strong><span><?= moneyFormat((float) $record['amount']) ?></span></div>
            <div><strong>Submitted</strong><span><?= htmlspecialchars((string) $record['created_at']) ?></span></div>
        </div>
        <div class="hero-actions"><a class="btn btn-primary" href="<?= url('/dashboard.php') ?>">Go to My Dashboard</a><a class="btn btn-ghost" href="<?= url('/properties.php') ?>">Browse More Properties</a></div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

