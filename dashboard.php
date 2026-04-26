<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Dashboard';
$extraStyles = [asset('/css/dashboard.css')];
$extraScripts = [asset('/js/validation.js')];
requireClientLogin();
require_once __DIR__ . '/includes/header.php';
$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT * FROM preferences WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$preferences = $stmt->fetch() ?: [];
$properties = $pdo->query('SELECT * FROM properties WHERE status = "active" ORDER BY created_at DESC')->fetchAll();
$matching = array_values(array_filter($properties, static fn(array $property): bool => propertyMatchesPreferences($property, $preferences)));
$stmt = $pdo->prepare('SELECT i.*, p.title, p.area_locality, p.price, p.primary_image, pay.status AS payment_status FROM interests i INNER JOIN properties p ON p.property_id = i.property_id LEFT JOIN payments pay ON pay.interest_id = i.interest_id WHERE i.user_id = ? ORDER BY i.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$interests = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT property_id FROM interests WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$interestPropertyIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
?>
<section class="section dashboard-hero">
    <div class="container">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Client') ?></h1>
        <p>Manage your preferences and review the properties matched for you.</p>
    </div>
</section>
<section class="section">
    <div class="container dashboard-grid">
        <div>
            <div class="panel">
                <div class="panel-header"><h2>My Preferences</h2><button class="btn btn-small" type="button" data-toggle-form>Edit Preferences</button></div>
                <?php if ($preferences) : ?>
                    <p><strong>Areas:</strong> <?= htmlspecialchars($preferences['preferred_areas'] ?: 'Any') ?></p>
                    <p><strong>Transaction:</strong> <?= htmlspecialchars($preferences['transaction_type']) ?></p>
                    <p><strong>BHK:</strong> <?= htmlspecialchars($preferences['bhk_type']) ?></p>
                    <p><strong>Budget:</strong> <?= moneyFormat((float) $preferences['min_budget']) ?> - <?= moneyFormat((float) $preferences['max_budget']) ?></p>
                <?php else : ?>
                    <p>No preferences saved yet.</p>
                <?php endif; ?>
            </div>
            <form class="panel form-grid hidden" data-preferences-form action="<?= url('/php/preferences.php?action=save') ?>" method="post" data-validate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <label>Preferred Areas <input class="form-input" type="text" name="preferred_areas" value="<?= htmlspecialchars($preferences['preferred_areas'] ?? '') ?>"></label>
                <fieldset><legend>Transaction Type</legend><label><input type="radio" name="transaction_type" value="rent" <?= checkedValue('rent', $preferences['transaction_type'] ?? 'rent') ?>> Rent</label><label><input type="radio" name="transaction_type" value="buy" <?= checkedValue('buy', $preferences['transaction_type'] ?? 'rent') ?>> Buy</label></fieldset>
                <label>BHK Type <select class="form-input" name="bhk_type"><option <?= selectedValue('1BHK', $preferences['bhk_type'] ?? '2BHK') ?>>1BHK</option><option <?= selectedValue('2BHK', $preferences['bhk_type'] ?? '2BHK') ?>>2BHK</option><option <?= selectedValue('3BHK', $preferences['bhk_type'] ?? '2BHK') ?>>3BHK</option><option <?= selectedValue('4BHK', $preferences['bhk_type'] ?? '2BHK') ?>>4BHK</option><option <?= selectedValue('Villa', $preferences['bhk_type'] ?? '2BHK') ?>>Villa</option></select></label>
                <div class="two-col"><label>Min Budget <input class="form-input" type="number" name="min_budget" value="<?= htmlspecialchars((string) ($preferences['min_budget'] ?? '')) ?>"></label><label>Max Budget <input class="form-input" type="number" name="max_budget" value="<?= htmlspecialchars((string) ($preferences['max_budget'] ?? '')) ?>"></label></div>
                <fieldset><legend>Property Type</legend><label><input type="radio" name="property_type" value="society" <?= checkedValue('society', $preferences['property_type'] ?? 'any') ?>> Society</label><label><input type="radio" name="property_type" value="standalone" <?= checkedValue('standalone', $preferences['property_type'] ?? 'any') ?>> Standalone Building</label><label><input type="radio" name="property_type" value="any" <?= checkedValue('any', $preferences['property_type'] ?? 'any') ?>> Any</label></fieldset>
                <label>Floor Preference <select class="form-input" name="floor_preference"><option value="any" <?= selectedValue('any', $preferences['floor_preference'] ?? 'any') ?>>Any</option><option value="ground" <?= selectedValue('ground', $preferences['floor_preference'] ?? 'any') ?>>Ground</option><option value="low" <?= selectedValue('low', $preferences['floor_preference'] ?? 'any') ?>>Low</option><option value="mid" <?= selectedValue('mid', $preferences['floor_preference'] ?? 'any') ?>>Mid</option><option value="high" <?= selectedValue('high', $preferences['floor_preference'] ?? 'any') ?>>High</option></select></label>
                <fieldset><legend>Amenities Required</legend><label><input type="checkbox" name="amenities_needed[]" value="swimming_pool"> Swimming Pool</label><label><input type="checkbox" name="amenities_needed[]" value="gym"> Gym</label><label><input type="checkbox" name="amenities_needed[]" value="garden"> Garden</label><label><input type="checkbox" name="amenities_needed[]" value="security"> Security</label><label><input type="checkbox" name="amenities_needed[]" value="parking"> Parking</label><label><input type="checkbox" name="amenities_needed[]" value="lift"> Lift</label><label><input type="checkbox" name="amenities_needed[]" value="clubhouse"> Clubhouse</label><label><input type="checkbox" name="amenities_needed[]" value="children_play_area"> Children's Play Area</label></fieldset>
                <fieldset><legend>Possession Type</legend><label><input type="radio" name="possession_type" value="ready" <?= checkedValue('ready', $preferences['possession_type'] ?? 'any') ?>> Ready to Move</label><label><input type="radio" name="possession_type" value="under_construction" <?= checkedValue('under_construction', $preferences['possession_type'] ?? 'any') ?>> Under Construction</label><label><input type="radio" name="possession_type" value="any" <?= checkedValue('any', $preferences['possession_type'] ?? 'any') ?>> Any</label></fieldset>
                <label>Additional Notes <textarea class="form-input" name="additional_notes" rows="3" maxlength="300"><?= htmlspecialchars($preferences['additional_notes'] ?? '') ?></textarea></label>
                <button class="btn btn-primary" type="submit">Save Preferences</button>
            </form>
        </div>
        <div class="panel">
            <div class="panel-header"><h2>Matching Properties</h2><span><?= count($matching) ?> found</span></div>
            <div class="property-grid compact-grid">
                <?php foreach (array_slice($matching, 0, 6) as $property) : ?>
                    <article class="property-card">
                        <img src="<?= htmlspecialchars(firstImageForProperty($pdo, $property)) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                        <div class="property-body">
                            <h3><?= htmlspecialchars($property['title']) ?></h3>
                            <p><?= htmlspecialchars($property['area_locality']) ?></p>
                            <div class="property-meta"><strong><?= moneyFormat((float) $property['price']) ?></strong><button type="button" class="btn btn-small interest-toggle <?= in_array((int) $property['property_id'], $interestPropertyIds, true) ? 'active' : '' ?>" data-property-id="<?= (int) $property['property_id'] ?>" data-csrf="<?= htmlspecialchars(csrfToken()) ?>"><?= in_array((int) $property['property_id'], $interestPropertyIds, true) ? 'Saved' : 'Save' ?></button><a class="btn btn-small" href="<?= url('/property-detail.php?id=' . (int) $property['property_id']) ?>">View Details</a></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<section class="section section-alt">
    <div class="container panel">
        <div class="panel-header"><h2>My Interests</h2></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Property</th><th>Area</th><th>Payment</th><th>Meeting</th><th>Created</th></tr></thead>
                <tbody>
                <?php foreach ($interests as $interest) : ?>
                    <tr>
                        <td><?= htmlspecialchars($interest['title']) ?></td>
                        <td><?= htmlspecialchars($interest['area_locality']) ?></td>
                        <td><?= htmlspecialchars($interest['payment_status'] ?? 'pending') ?></td>
                        <td><?= htmlspecialchars($interest['status']) ?></td>
                        <td><?= htmlspecialchars($interest['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
