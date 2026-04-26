<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
requireAdminLogin();
requirePostRequest();

$pdo = getDBConnection();
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirectWithMessage('/admin/interests.php', 'error', 'Security token expired.');
}

$interestId = (int) ($_POST['interest_id'] ?? 0);
$status = (string) ($_POST['status'] ?? 'pending');
$allowed = ['pending','call_scheduled','site_visit_scheduled','completed','cancelled'];
if (!in_array($status, $allowed, true)) {
    $status = 'pending';
}

$scheduledRaw = trim((string) ($_POST['scheduled_datetime'] ?? ''));
$scheduled = null;
if ($scheduledRaw !== '') {
    $scheduled = str_replace('T', ' ', $scheduledRaw);
    if (strlen($scheduled) === 16) {
        $scheduled .= ':00';
    }
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $scheduled);
    if ($dt === false) {
        redirectWithMessage('/admin/interests.php', 'error', 'Invalid scheduled date/time.');
    }
    $scheduled = $dt->format('Y-m-d H:i:s');
}

$remarks = sanitize((string) ($_POST['admin_remarks'] ?? ''));

if ($interestId <= 0) {
    redirectWithMessage('/admin/interests.php', 'error', 'Invalid interest request.');
}

$stmt = $pdo->prepare('UPDATE interests SET status = ?, scheduled_datetime = ?, admin_remarks = ? WHERE interest_id = ?');
$stmt->execute([$status, $scheduled, $remarks, $interestId]);
redirectWithMessage('/admin/interests.php', 'success', 'Status updated.');
