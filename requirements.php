<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$pdo = getDBConnection();

requirePostRequest();

try {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        redirectWithMessage('/index.php#quick-form', 'error', 'Security token expired. Please try again.');
    }

    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: null;
    $area = sanitize($_POST['area'] ?? '');
    $transactionType = ($_POST['transaction_type'] ?? 'rent') === 'buy' ? 'buy' : 'rent';
    $bhkType = sanitize($_POST['bhk_type'] ?? '2BHK');
    $minBudget = max(0, (float) ($_POST['min_budget'] ?? 0));
    $maxBudget = max($minBudget, (float) ($_POST['max_budget'] ?? 0));

    if ($fullName === '' || !indianPhoneValid($phone) || $area === '') {
        redirectWithMessage('/index.php#quick-form', 'error', 'Please provide your name, phone, and area.');
    }

    $userId = $_SESSION['user_id'] ?? null;
    $stmt = $pdo->prepare('INSERT INTO search_requests (full_name, phone, email, area, transaction_type, bhk_type, min_budget, max_budget, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$fullName, $phone, $email, $area, $transactionType, $bhkType, $minBudget, $maxBudget, $userId]);
    $reqId = (int) $pdo->lastInsertId();

    $_SESSION['guest_preview_req_id'] = $reqId;
    header('Location: ' . url('/properties.php?guest=1&req_id=' . $reqId));
    exit;
} catch (Throwable $e) {
    error_log($e->getMessage());
    redirectWithMessage('/index.php#quick-form', 'error', 'Unable to process your request right now.');
}
