<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Properties';
$extraScripts = [asset('/js/filters.js')];
require_once __DIR__ . '/includes/header.php';
$pdo = getDBConnection();
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$where = ['status = "active"'];
$params = [];
if (!empty($_GET['type']) && in_array($_GET['type'], ['rent', 'buy'], true)) { $where[] = 'transaction_type = ?'; $params[] = $_GET['type']; }
if (!empty($_GET['bhk'])) { $where[] = 'bhk_type = ?'; $params[] = sanitize((string) $_GET['bhk']); }
if (!empty($_GET['area'])) { $where[] = '(area_locality LIKE ? OR city LIKE ?)'; $params[] = '%' . sanitize((string) $_GET['area']) . '%'; $params[] = '%' . sanitize((string) $_GET['area']) . '%'; }
if (!empty($_GET['min_price'])) { $where[] = 'price >= ?'; $params[] = (float) $_GET['min_price']; }
if (!empty($_GET['max_price'])) { $where[] = 'price <= ?'; $params[] = (float) $_GET['max_price']; }
$sort = $_GET['sort'] ?? 'newest';
$orderBy = 'created_at DESC';
if ($sort === 'price_asc') { $orderBy = 'price ASC'; }
elseif ($sort === 'price_desc') { $orderBy = 'price DESC'; }
$sql = 'SELECT * FROM properties WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $orderBy;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allProperties = $stmt->fetchAll();
$amenityFilters = $_GET['amenities'] ?? [];
if (is_string($amenityFilters)) { $amenityFilters = [$amenityFilters]; }
$amenityFilters = array_filter(array_map('sanitize', (array) $amenityFilters));
if ($amenityFilters) {
    $allProperties = array_values(array_filter($allProperties, static function (array $property) use ($amenityFilters): bool {
        $have = json_decode((string) ($property['amenities'] ?? '[]'), true);
        $have = is_array($have) ? $have : [];
        return count(array_intersect($amenityFilters, $have)) === count($amenityFilters);
    }));
}
$total = count($allProperties);
$guestMode = !empty($_GET['guest']);
if ($guestMode) {
    $allProperties = array_slice($allProperties, 0, 3);
}
$offset = ($page - 1) * $perPage;
$pageItems = array_slice($allProperties, $offset, $perPage);
$totalPages = max(1, (int) ceil(max(1, count($allProperties)) / $perPage));
$queryBase = $_GET;
unset($queryBase['page']);
?>
<section class="section listing-layout">
    <div class="container listing-shell">
        <aside class="panel filter-sidebar">
            <h2>Filters</h2>
            <form id="filter-form" method="get" action="<?= url('/properties.php') ?>">
                <?php if ($guestMode) : ?><input type="hidden" name="guest" value="1"><?php endif; ?>
                <label>Transaction Type <select class="form-input" name="type"><option value="">Any</option><option value="rent" <?= selectedValue('rent', $_GET['type'] ?? '') ?>>Rent</option><option value="buy" <?= selectedValue('buy', $_GET['type'] ?? '') ?>>Buy</option></select></label>
                <label>BHK <select class="form-input" name="bhk"><option value="">Any</option><option>1BHK</option><option>2BHK</option><option>3BHK</option><option>4BHK</option><option>Villa</option></select></label>
                <label>Area <input class="form-input" type="text" name="area" value="<?= htmlspecialchars($_GET['area'] ?? '') ?>" list="areas"></label>
                <datalist id="areas"><option>Kothrud</option><option>Baner</option><option>Wakad</option><option>Aundh</option><option>Viman Nagar</option><option>Hadapsar</option></datalist>
                <div class="two-col"><label>Min <input class="form-input" type="number" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>"></label><label>Max <input class="form-input" type="number" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>"></label></div>
                <fieldset><legend>Amenities</legend><label><input type="checkbox" name="amenities[]" value="swimming_pool" <?= in_array('swimming_pool', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Swimming Pool</label><label><input type="checkbox" name="amenities[]" value="gym" <?= in_array('gym', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Gym</label><label><input type="checkbox" name="amenities[]" value="garden" <?= in_array('garden', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Garden</label><label><input type="checkbox" name="amenities[]" value="security" <?= in_array('security', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Security</label><label><input type="checkbox" name="amenities[]" value="parking" <?= in_array('parking', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Parking</label><label><input type="checkbox" name="amenities[]" value="lift" <?= in_array('lift', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Lift</label><label><input type="checkbox" name="amenities[]" value="clubhouse" <?= in_array('clubhouse', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Clubhouse</label><label><input type="checkbox" name="amenities[]" value="children_play_area" <?= in_array('children_play_area', (array) ($_GET['amenities'] ?? []), true) ? 'checked' : '' ?>> Children's Play Area</label></fieldset>
                <button class="btn btn-primary" type="submit">Apply Filters</button>
                <a class="btn btn-ghost" href="<?= url('/properties.php') ?>">Clear Filters</a>
            </form>
        </aside>
        <div class="results-main">
            <?php if ($guestMode) : ?><div class="banner">You are viewing a limited preview. Guest mode shows the first 3 matching properties. Sign up to see the full list.</div><?php endif; ?>
            <div class="panel panel-tight results-toolbar"><strong>Showing <?= count($pageItems) ?> of <?= $total ?> matching properties</strong><form method="get" class="inline-form"><input type="hidden" name="type" value="<?= htmlspecialchars($_GET['type'] ?? '') ?>"><input type="hidden" name="bhk" value="<?= htmlspecialchars($_GET['bhk'] ?? '') ?>"><input type="hidden" name="area" value="<?= htmlspecialchars($_GET['area'] ?? '') ?>"><input type="hidden" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>"><input type="hidden" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>"><input type="hidden" name="guest" value="<?= htmlspecialchars($_GET['guest'] ?? '') ?>"><?php foreach ((array) ($_GET['amenities'] ?? []) as $amenity) : ?><input type="hidden" name="amenities[]" value="<?= htmlspecialchars((string) $amenity) ?>"><?php endforeach; ?><label>Sort <select class="form-input" name="sort"><option value="newest" <?= selectedValue('newest', $_GET['sort'] ?? 'newest') ?>>Newest First</option><option value="price_asc" <?= selectedValue('price_asc', $_GET['sort'] ?? 'newest') ?>>Price: Low to High</option><option value="price_desc" <?= selectedValue('price_desc', $_GET['sort'] ?? 'newest') ?>>Price: High to Low</option></select></label><button class="btn btn-small" type="submit">Sort</button></form><span>Page <?= $page ?> / <?= $totalPages ?></span></div>
            <?php if (!$pageItems) : ?><div class="panel">No properties found matching your criteria. Try adjusting your filters.</div><?php else : ?>
                <div class="property-grid">
                    <?php foreach ($pageItems as $property) : ?>
                        <article class="property-card">
                            <img src="<?= htmlspecialchars(firstImageForProperty($pdo, $property)) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                            <div class="property-body">
                                <div class="chip-row"><span class="chip"><?= htmlspecialchars($property['area_locality']) ?></span><span class="chip"><?= htmlspecialchars($property['bhk_type']) ?></span></div>
                                <h3><?= htmlspecialchars($property['title']) ?></h3>
                                <p><?= htmlspecialchars($property['transaction_type']) ?> • Floor <?= htmlspecialchars((string) ($property['floor_number'] ?? 'Any')) ?></p>
                                <?php $amenitySummary = array_slice(json_decode((string) ($property['amenities'] ?? '[]'), true) ?: [], 0, 3); ?>
                                <div class="chip-row wrap-row"><?php foreach ($amenitySummary as $amenity) : ?><span class="chip"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $amenity))) ?></span><?php endforeach; ?></div>
                                <div class="property-meta"><strong><?= moneyFormat((float) $property['price']) ?></strong><a class="btn btn-small" href="<?= url('/property-detail.php?id=' . (int) $property['property_id']) ?>">View Details</a></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++) : $q = $queryBase; $q['page'] = $i; ?>
                    <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="<?= url('/properties.php?' . http_build_query($q)) ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

