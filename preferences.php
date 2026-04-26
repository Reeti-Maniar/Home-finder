<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
requireClientLogin();

$action = $_GET['action'] ?? '';
$pdo = getDBConnection();

requirePostRequest();

try {
    if ($action !== 'save') {
        http_response_code(400);
        echo 'Unsupported action.';
        exit;
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        redirectWithMessage('/dashboard.php', 'error', 'Security token expired. Please try again.');
    }

    $transactionType = ($_POST['transaction_type'] ?? 'rent') === 'buy' ? 'buy' : 'rent';
    $bhkType = sanitize($_POST['bhk_type'] ?? '2BHK');
    $propertyType = in_array(($_POST['property_type'] ?? 'any'), ['society', 'standalone', 'any'], true) ? $_POST['property_type'] : 'any';
    $floorPreference = in_array(($_POST['floor_preference'] ?? 'any'), ['any', 'ground', 'low', 'mid', 'high'], true) ? $_POST['floor_preference'] : 'any';
    $possessionType = in_array(($_POST['possession_type'] ?? 'any'), ['ready', 'under_construction', 'any'], true) ? $_POST['possession_type'] : 'any';
    $preferredAreas = sanitize((string) ($_POST['preferred_areas'] ?? ''));
    $minBudget = max(0, (float) ($_POST['min_budget'] ?? 0));
    $maxBudget = max($minBudget, (float) ($_POST['max_budget'] ?? 0));
    $notes = sanitize((string) ($_POST['additional_notes'] ?? ''));

    $amenities = $_POST['amenities_needed'] ?? [];
    if (!is_array($amenities)) {
        $amenities = [];
    }
    $amenities = array_values(array_map('sanitize', $amenities));

    $stmt = $pdo->prepare(
        'INSERT INTO preferences (user_id, transaction_type, bhk_type, preferred_areas, min_budget, max_budget, property_type, floor_preference, possession_type, amenities_needed, additional_notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           transaction_type = VALUES(transaction_type),
           bhk_type = VALUES(bhk_type),
           preferred_areas = VALUES(preferred_areas),
           min_budget = VALUES(min_budget),
           max_budget = VALUES(max_budget),
           property_type = VALUES(property_type),
           floor_preference = VALUES(floor_preference),
           possession_type = VALUES(possession_type),
           amenities_needed = VALUES(amenities_needed),
           additional_notes = VALUES(additional_notes)'
    );
    $stmt->execute([
        $_SESSION['user_id'],
        $transactionType,
        $bhkType,
        $preferredAreas,
        $minBudget,
        $maxBudget,
        $propertyType,
        $floorPreference,
        $possessionType,
        json_encode($amenities),
        $notes,
    ]);

    redirectWithMessage('/dashboard.php', 'success', 'Preferences saved successfully.');
} catch (Throwable $e) {
    error_log($e->getMessage());
    redirectWithMessage('/dashboard.php', 'error', 'Unable to save preferences right now.');
}
