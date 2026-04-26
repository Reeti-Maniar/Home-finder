<?php
require_once __DIR__ . '/../includes/init.php';
$pageTitle = 'HomeFinder Admin | Property Form';
$extraStyles = [asset('/css/admin.css')];
$extraScripts = [asset('/js/validation.js')];
requireAdminLogin();
require_once __DIR__ . '/../includes/header.php';
$pdo = getDBConnection();
$property = [
    'title' => '', 'description' => '', 'property_type' => 'apartment', 'transaction_type' => 'rent', 'bhk_type' => '2BHK', 'price' => '',
    'area_locality' => '', 'full_address' => '', 'city' => 'Pune', 'floor_number' => '', 'total_floors' => '', 'carpet_area_sqft' => '',
    'facing_direction' => 'any', 'furnishing_status' => 'furnished', 'possession_type' => 'ready', 'society_type' => 'society', 'is_featured' => 0, 'status' => 'active', 'amenities' => [],
];
if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM properties WHERE property_id = ?');
    $stmt->execute([(int) $_GET['id']]);
    $property = $stmt->fetch() ?: $property;
    $property['amenities'] = json_decode((string) ($property['amenities'] ?? '[]'), true) ?: [];
}
$amenityOptions = ['swimming_pool' => 'Swimming Pool','gym' => 'Gym','garden' => 'Garden','security' => 'Security','parking' => 'Parking','lift' => 'Lift','clubhouse' => 'Clubhouse','children_play_area' => 'Children\'s Play Area'];
?>
<section class="section admin-shell">
    <div class="container admin-grid">
        <aside class="panel admin-sidebar">
            <h1><?= !empty($_GET['id']) ? 'Edit Property' : 'Add Property' ?></h1>
            <a href="<?= url('/admin/dashboard.php') ?>">Dashboard</a>
            <a href="<?= url('/admin/properties.php') ?>">Manage Properties</a>
            <a href="<?= url('/admin/clients.php') ?>">Manage Clients</a>
            <a href="<?= url('/admin/interests.php') ?>">Interest Requests</a>
        </aside>
        <div class="admin-main">
            <form class="panel form-grid" action="<?= url('/php/properties.php?action=save') ?>" method="post" enctype="multipart/form-data" data-validate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="property_id" value="<?= htmlspecialchars((string) ($_GET['id'] ?? '')) ?>">
                <label>Property Title <input class="form-input" type="text" name="title" value="<?= htmlspecialchars($property['title']) ?>" required></label>
                <label>Description <textarea class="form-input" name="description" rows="4" required><?= htmlspecialchars($property['description']) ?></textarea></label>
                <div class="two-col"><label>Property Type <select class="form-input" name="property_type"><option value="apartment" <?= selectedValue('apartment', $property['property_type']) ?>>Apartment</option><option value="villa" <?= selectedValue('villa', $property['property_type']) ?>>Villa</option><option value="independent_house" <?= selectedValue('independent_house', $property['property_type']) ?>>Independent House</option><option value="studio" <?= selectedValue('studio', $property['property_type']) ?>>Studio</option></select></label><label>Transaction Type <select class="form-input" name="transaction_type"><option value="rent" <?= selectedValue('rent', $property['transaction_type']) ?>>Rent</option><option value="buy" <?= selectedValue('buy', $property['transaction_type']) ?>>Buy</option></select></label></div>
                <div class="two-col"><label>BHK Type <select class="form-input" name="bhk_type"><option <?= selectedValue('1BHK', $property['bhk_type']) ?>>1BHK</option><option <?= selectedValue('2BHK', $property['bhk_type']) ?>>2BHK</option><option <?= selectedValue('3BHK', $property['bhk_type']) ?>>3BHK</option><option <?= selectedValue('4BHK', $property['bhk_type']) ?>>4BHK</option><option <?= selectedValue('Villa', $property['bhk_type']) ?>>Villa</option><option <?= selectedValue('Studio', $property['bhk_type']) ?>>Studio</option></select></label><label>Price <input class="form-input" type="number" name="price" value="<?= htmlspecialchars((string) $property['price']) ?>" required></label></div>
                <div class="two-col"><label>Area / Locality <input class="form-input" type="text" name="area_locality" value="<?= htmlspecialchars($property['area_locality']) ?>" required></label><label>City <input class="form-input" type="text" name="city" value="<?= htmlspecialchars($property['city']) ?>" required></label></div>
                <label>Full Address <textarea class="form-input" name="full_address" rows="3" required><?= htmlspecialchars($property['full_address']) ?></textarea></label>
                <div class="two-col"><label>Floor Number <input class="form-input" type="number" name="floor_number" value="<?= htmlspecialchars((string) $property['floor_number']) ?>"></label><label>Total Floors <input class="form-input" type="number" name="total_floors" value="<?= htmlspecialchars((string) $property['total_floors']) ?>"></label></div>
                <div class="two-col"><label>Carpet Area <input class="form-input" type="number" name="carpet_area_sqft" value="<?= htmlspecialchars((string) $property['carpet_area_sqft']) ?>"></label><label>Facing Direction <select class="form-input" name="facing_direction"><option value="any" <?= selectedValue('any', $property['facing_direction']) ?>>Any</option><option value="east" <?= selectedValue('east', $property['facing_direction']) ?>>East</option><option value="west" <?= selectedValue('west', $property['facing_direction']) ?>>West</option><option value="north" <?= selectedValue('north', $property['facing_direction']) ?>>North</option><option value="south" <?= selectedValue('south', $property['facing_direction']) ?>>South</option></select></label></div>
                <div class="two-col"><label>Furnishing Status <select class="form-input" name="furnishing_status"><option value="furnished" <?= selectedValue('furnished', $property['furnishing_status']) ?>>Furnished</option><option value="semi_furnished" <?= selectedValue('semi_furnished', $property['furnishing_status']) ?>>Semi-Furnished</option><option value="unfurnished" <?= selectedValue('unfurnished', $property['furnishing_status']) ?>>Unfurnished</option></select></label><label>Possession Type <select class="form-input" name="possession_type"><option value="ready" <?= selectedValue('ready', $property['possession_type']) ?>>Ready to Move</option><option value="under_construction" <?= selectedValue('under_construction', $property['possession_type']) ?>>Under Construction</option></select></label></div>
                <fieldset><legend>Society / Standalone</legend><label><input type="radio" name="society_type" value="society" <?= checkedValue('society', $property['society_type']) ?>> Society</label><label><input type="radio" name="society_type" value="standalone" <?= checkedValue('standalone', $property['society_type']) ?>> Standalone</label></fieldset>
                <fieldset><legend>Amenities</legend><?php foreach ($amenityOptions as $value => $label) : ?><label><input type="checkbox" name="amenities[]" value="<?= htmlspecialchars($value) ?>" <?= in_array($value, (array) $property['amenities'], true) ? 'checked' : '' ?>> <?= htmlspecialchars($label) ?></label><?php endforeach; ?></fieldset>
                <label>Property Images <input class="form-input" type="file" name="property_images[]" multiple accept="image/png,image/jpeg,image/webp"></label>
                <label><input type="checkbox" name="is_featured" value="1" <?= !empty($property['is_featured']) ? 'checked' : '' ?>> Is Featured</label>
                <label>Status <select class="form-input" name="status"><option value="active" <?= selectedValue('active', $property['status']) ?>>Active</option><option value="inactive" <?= selectedValue('inactive', $property['status']) ?>>Inactive</option></select></label>
                <button class="btn btn-primary" type="submit">Save Property</button>
            </form>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
