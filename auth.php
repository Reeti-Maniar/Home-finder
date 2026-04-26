<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$action = $_GET['action'] ?? '';
$pdo = getDBConnection();

function authFail(string $url, string $message): void
{
    redirectWithMessage($url, 'error', $message);
}

try {
    if ($action === 'register') {
        requirePostRequest();
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            authFail('/register.php', 'Security token expired. Please try again.');
        }

        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (!$fullName || !$email || !indianPhoneValid($phone) || !passwordStrong($password) || $password !== $confirm) {
            authFail('/register.php', 'Please complete the registration form with valid details.');
        }

        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            authFail('/register.php', 'An account with this email already exists.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([$fullName, $email, $phone, $hash]);

        $_SESSION['user_id'] = (int) $pdo->lastInsertId();
        $_SESSION['user_name'] = $fullName;
        $_SESSION['role'] = 'client';
        session_regenerate_id(true);

        redirectWithMessage('/dashboard.php', 'success', 'Account created successfully.');
    }

    if ($action === 'login') {
        requirePostRequest();
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            authFail('/login.php', 'Security token expired. Please try again.');
        }

        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');

        if (!$email || $password === '') {
            authFail('/login.php', 'Invalid email or password.');
        }

        $stmt = $pdo->prepare('SELECT user_id, full_name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            authFail('/login.php', 'Invalid email or password.');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = 'client';

        $redirect = $_GET['redirect'] ?? '/dashboard.php';
        header('Location: ' . url($redirect));
        exit;
    }

    if ($action === 'admin_register') {
        requirePostRequest();
        if (($_GET['secret'] ?? '') !== ADMIN_SECRET) {
            http_response_code(403);
            echo 'Access Denied.';
            exit;
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            authFail('/admin/register.php?secret=' . urlencode(ADMIN_SECRET), 'Security token expired. Please try again.');
        }

        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (!$fullName || !$email || !passwordStrong($password) || $password !== $confirm) {
            authFail('/admin/register.php?secret=' . urlencode(ADMIN_SECRET), 'Please complete the admin registration form correctly.');
        }

        $stmt = $pdo->prepare('SELECT admin_id FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            authFail('/admin/register.php?secret=' . urlencode(ADMIN_SECRET), 'An account with this email already exists.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO admins (full_name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$fullName, $email, $hash]);

        redirectWithMessage('/admin/login.php', 'success', 'Admin account created. Please log in.');
    }

    if ($action === 'admin_login') {
        requirePostRequest();
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            authFail('/admin/login.php', 'Security token expired. Please try again.');
        }

        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');

        if (!$email || $password === '') {
            authFail('/admin/login.php', 'Invalid email or password.');
        }

        $stmt = $pdo->prepare('SELECT admin_id, full_name, password_hash FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            usleep(800000);
            authFail('/admin/login.php', 'Invalid email or password.');
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['role'] = 'admin';

        header('Location: ' . url('/admin/dashboard.php'));
        exit;
    }

    http_response_code(400);
    echo 'Unsupported action.';
} catch (Throwable $e) {
    error_log($e->getMessage());
    authFail($action === 'admin_login' ? '/admin/login.php' : '/login.php', 'Something went wrong. Please try again.');
}
