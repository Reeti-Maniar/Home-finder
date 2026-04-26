<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Property Detail';
require_once __DIR__ . '/includes/header.php';
$pdo = getDBConnection();
$propertyId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM properties WHERE property_id = ? AND status = "active"');
$stmt->execute([$propertyId]);
$property = $stmt->fetch();
if (!$property) {
    echo '<section class="section"><div class="container panel">Property not found.</div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$images = propertyImageGallery($pdo, $propertyId);
if (!$images) { $images = [firstImageForProperty($pdo, $property)]; }
$amenities = json_decode((string) ($property['amenities'] ?? '[]'), true);
$amenities = is_array($amenities) ? $amenities : [];
?>
<section class="section">
    <div class="container detail-layout">
        <article class="panel detail-main">
            <div class="gallery">
                <img class="gallery-main" id="main-image" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                <div class="gallery-thumbs">
                    <?php foreach (array_slice($images, 0, 5) as $image) : ?><button type="button" class="thumb-btn" data-image="<?= htmlspecialchars($image) ?>"><img src="<?= htmlspecialchars($image) ?>" alt="thumb"></button><?php endforeach; ?>
                </div>
            </div>
            <div class="detail-header"><div><h1><?= htmlspecialchars($property['title']) ?></h1><p><?= htmlspecialchars($property['area_locality']) ?>, <?= htmlspecialchars($property['city']) ?></p><div class="chip-row"><span class="chip"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $property['possession_type']))) ?></span><span class="chip"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $property['society_type']))) ?></span></div></div><div class="price-tag"><?= moneyFormat((float) $property['price']) ?></div></div>
            <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
            <div class="detail-grid">
                <div><strong>Property Type</strong><span><?= htmlspecialchars($property['property_type']) ?></span></div>
                <div><strong>BHK</strong><span><?= htmlspecialchars($property['bhk_type']) ?></span></div>
                <div><strong>Floor</strong><span><?= htmlspecialchars((string) ($property['floor_number'] ?? 'Any')) ?></span></div>
                <div><strong>Facing</strong><span><?= htmlspecialchars($property['facing_direction']) ?></span></div>
                <div><strong>Furnishing</strong><span><?= htmlspecialchars($property['furnishing_status']) ?></span></div>
                <div><strong>Society Type</strong><span><?= htmlspecialchars($property['society_type']) ?></span></div>
            </div>
            <h2>Amenities</h2>
            <div class="chip-row wrap-row"><?php foreach ($amenities as $item) : ?><span class="chip"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $item))) ?></span><?php endforeach; ?></div>
            <h2>Location</h2>
            <p><?= !empty($_SESSION['user_id']) ? htmlspecialchars($property['full_address']) : 'Login to see full address.' ?></p>
        </article>
        <aside class="panel sticky-card">
            <h2><?= moneyFormat((float) $property['price']) ?></h2>
            <p>Confirmation flow starts from this page.</p>
            <a class="btn btn-primary btn-full" href="<?= !empty($_SESSION['user_id']) ? url('/payment.php?property_id=' . $propertyId) : url('/login.php?redirect=' . urlencode('/property-detail.php?id=' . $propertyId)) ?>">I'm Interested</a>
        </aside>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

