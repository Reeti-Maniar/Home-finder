<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = 'HomeFinder Admin | Client Profile';
$extraStyles = [asset('/css/admin.css')];

requireAdminLogin();
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
$userId = (int) ($_GET['user_id'] ?? 0);

$stmt = $pdo->prepare('SELECT user_id, full_name, email, phone, created_at, updated_at FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$client = $stmt->fetch();

if (!$client) {
    echo '<section class="section"><div class="container panel">Client not found.</div></section>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$prefStmt = $pdo->prepare('SELECT * FROM preferences WHERE user_id = ?');
$prefStmt->execute([$userId]);
$preferences = $prefStmt->fetch() ?: [];

$interestStmt = $pdo->prepare(
    'SELECT i.interest_id, i.status, i.created_at, i.updated_at, i.scheduled_datetime, i.admin_remarks,
            p.title, p.area_locality, p.price,
            pay.status AS payment_status, pay.amount
     FROM interests i
     INNER JOIN properties p ON p.property_id = i.property_id
     LEFT JOIN payments pay ON pay.interest_id = i.interest_id
     WHERE i.user_id = ?
     ORDER BY i.created_at DESC'
);
$interestStmt->execute([$userId]);
$interests = $interestStmt->fetchAll();

$amenities = decodeJsonArray($preferences['amenities_needed'] ?? '');
$areas = normalizeCommaList((string) ($preferences['preferred_areas'] ?? ''));
?>
<section class="section admin-shell">
    <div class="container admin-grid">
        <aside class="panel admin-sidebar">
            <h1>Client Profile</h1>
            <a href="<?= url('/admin/dashboard.php') ?>">Dashboard</a>
            <a href="<?= url('/admin/clients.php') ?>">Back to Clients</a>
            <a href="<?= url('/admin/interests.php') ?>">Interest Requests</a>
        </aside>

        <div class="admin-main">
            <div class="panel">
                <div class="panel-header">
                    <h2><?= htmlspecialchars($client['full_name']) ?></h2>
                    <span>Registered <?= htmlspecialchars($client['created_at']) ?></span>
                </div>

                <div class="metric-grid">
                    <article class="panel metric-card"><span>Email</span><strong><?= htmlspecialchars($client['email']) ?></strong></article>
                    <article class="panel metric-card"><span>Phone</span><strong><?= htmlspecialchars($client['phone']) ?></strong></article>
                    <article class="panel metric-card"><span>Total Interests</span><strong><?= count($interests) ?></strong></article>
                    <article class="panel metric-card"><span>Preferences</span><strong><?= $preferences ? 'Saved' : 'Not Set' ?></strong></article>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2>Saved Preferences</h2>
                </div>

                <?php if (!$preferences) : ?>
                    <p>This client has not saved any preferences yet.</p>
                <?php else : ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <tbody>
                                <tr><th>Transaction Type</th><td><?= htmlspecialchars((string) ($preferences['transaction_type'] ?? '')) ?></td></tr>
                                <tr><th>BHK Type</th><td><?= htmlspecialchars((string) ($preferences['bhk_type'] ?? '')) ?></td></tr>
                                <tr><th>Property Type</th><td><?= htmlspecialchars((string) ($preferences['property_type'] ?? '')) ?></td></tr>
                                <tr><th>Budget</th><td><?= moneyFormat((float) ($preferences['min_budget'] ?? 0)) ?> to <?= moneyFormat((float) ($preferences['max_budget'] ?? 0)) ?></td></tr>
                                <tr><th>Preferred Areas</th><td><?= htmlspecialchars(implode(', ', $areas) ?: 'Any') ?></td></tr>
                                <tr><th>Floor Preference</th><td><?= htmlspecialchars((string) ($preferences['floor_preference'] ?? 'any')) ?></td></tr>
                                <tr><th>Possession Type</th><td><?= htmlspecialchars((string) ($preferences['possession_type'] ?? 'any')) ?></td></tr>
                                <tr><th>Amenities Needed</th><td><?= htmlspecialchars(implode(', ', array_map(static fn($item) => ucwords(str_replace('_', ' ', (string) $item)), $amenities)) ?: 'None') ?></td></tr>
                                <tr><th>Notes</th><td><?= nl2br(htmlspecialchars((string) ($preferences['additional_notes'] ?? ''))) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2>Interest History</h2>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Area</th>
                                <th>Payment</th>
                                <th>Meeting</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interests as $row) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['area_locality']) ?></td>
                                    <td><?= htmlspecialchars((string) ($row['payment_status'] ?? 'pending')) ?> / <?= moneyFormat((float) ($row['amount'] ?? 0)) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
