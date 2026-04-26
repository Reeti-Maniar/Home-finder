<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
requireAdminLogin();
requirePostRequest();

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirectWithMessage('/admin/properties.php', 'error', 'Security token expired.');
}

$propertyId = (int) ($_POST['id'] ?? 0);
if ($propertyId <= 0) {
    redirectWithMessage('/admin/properties.php', 'error', 'Invalid property.');
}

$pdo = getDBConnection();
$stmt = $pdo->prepare('DELETE FROM properties WHERE property_id = ?');
$stmt->execute([$propertyId]);
redirectWithMessage('/admin/properties.php', 'success', 'Property deleted successfully.');
