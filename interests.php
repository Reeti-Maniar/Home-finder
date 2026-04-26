<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';

function jsonOut(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

try {
    if ($action === 'toggle') {
        requirePostRequest();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
            jsonOut(['status' => 'not_logged_in', 'redirect' => url('/login.php')]);
        }

        if (!verifyCsrfToken((string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
            jsonOut(['status' => 'error', 'message' => 'Security token expired.'], 419);
        }

        $body = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
        $propertyId = (int) ($body['property_id'] ?? 0);
        if ($propertyId <= 0) {
            jsonOut(['status' => 'error', 'message' => 'Invalid property.'], 400);
        }

        $stmt = $pdo->prepare('SELECT i.interest_id, pay.payment_id FROM interests i LEFT JOIN payments pay ON pay.interest_id = i.interest_id WHERE i.user_id = ? AND i.property_id = ?');
        $stmt->execute([$_SESSION['user_id'], $propertyId]);
        $existing = $stmt->fetch();

        if ($existing) {
            if (!empty($existing['payment_id'])) {
                jsonOut(['status' => 'locked']);
            }

            $pdo->prepare('DELETE FROM interests WHERE interest_id = ?')->execute([$existing['interest_id']]);
            jsonOut(['status' => 'removed']);
        }

        $pdo->prepare('INSERT INTO interests (user_id, property_id, status) VALUES (?, ?, "pending")')->execute([$_SESSION['user_id'], $propertyId]);
        jsonOut(['status' => 'added']);
    }

    if ($action === 'confirm') {
        requirePostRequest();
        requireClientLogin();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            redirectWithMessage('/payment.php?property_id=' . (int) ($_POST['property_id'] ?? 0), 'error', 'Security token expired. Please try again.');
        }

        $propertyId = (int) ($_POST['property_id'] ?? 0);
        $cardName = sanitize((string) ($_POST['card_name'] ?? ''));
        $cardNumber = preg_replace('/\D+/', '', (string) ($_POST['card_number'] ?? ''));
        $cardExpiry = sanitize((string) ($_POST['card_expiry'] ?? ''));
        $cardCvv = preg_replace('/\D+/', '', (string) ($_POST['card_cvv'] ?? ''));

        if ($propertyId <= 0 || $cardName === '' || strlen($cardNumber) !== 16 || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cardExpiry) || strlen($cardCvv) !== 3) {
            redirectWithMessage('/payment.php?property_id=' . $propertyId, 'error', 'Please enter valid simulated payment details.');
        }

        $stmt = $pdo->prepare('SELECT property_id FROM properties WHERE property_id = ? AND status = "active"');
        $stmt->execute([$propertyId]);
        $property = $stmt->fetch();
        if (!$property) {
            redirectWithMessage('/properties.php', 'error', 'Property not found.');
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT interest_id FROM interests WHERE user_id = ? AND property_id = ?');
        $stmt->execute([$_SESSION['user_id'], $propertyId]);
        $interest = $stmt->fetch();

        if ($interest) {
            $interestId = (int) $interest['interest_id'];
            $pdo->prepare('UPDATE interests SET status = "pending", updated_at = NOW() WHERE interest_id = ?')->execute([$interestId]);
        } else {
            $pdo->prepare('INSERT INTO interests (user_id, property_id, status) VALUES (?, ?, "pending")')->execute([$_SESSION['user_id'], $propertyId]);
            $interestId = (int) $pdo->lastInsertId();
        }

        $transactionRef = 'SIM-' . time() . '-' . (int) $_SESSION['user_id'];
        $stmt = $pdo->prepare('INSERT INTO payments (interest_id, user_id, amount, payment_method, status, transaction_ref) VALUES (?, ?, 499.00, "simulated", "paid", ?) ON DUPLICATE KEY UPDATE amount = VALUES(amount), payment_method = VALUES(payment_method), status = VALUES(status), transaction_ref = VALUES(transaction_ref)');
        $stmt->execute([$interestId, $_SESSION['user_id'], $transactionRef]);
        $pdo->commit();

        header('Location: ' . url('/payment-success.php?interest_id=' . $interestId));
        exit;
    }

    http_response_code(400);
    echo 'Unsupported action.';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    redirectWithMessage('/properties.php', 'error', 'Something went wrong while saving your interest.');
}
