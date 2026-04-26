<?php
declare(strict_types=1);

function safeInternalPath(string $path, string $fallback = '/index.php'): string
{
    $path = trim($path);
    $path = str_replace(["\r", "\n", "\\"], ['', '', '/'], $path);

    if ($path === '' || str_contains($path, '://') || str_starts_with($path, '//') || str_contains($path, '..')) {
        return $fallback;
    }

    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }

    return $path;
}

function url(string $path = ''): string
{
    $path = safeInternalPath($path, '/');
    return rtrim(APP_BASE_PATH, '/') . $path;
}

function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function redirectTo(string $path, int $statusCode = 302): void
{
    header('Location: ' . url($path), true, $statusCode);
    exit;
}

function redirectWithMessage(string $path, string $type, string $message): void
{
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
    redirectTo($path, 303);
}

function flash(): ?array
{
    if (empty($_SESSION['flash_message'])) {
        return null;
    }

    $flash = [
        'type' => $_SESSION['flash_type'] ?? 'info',
        'message' => $_SESSION['flash_message'],
    ];

    unset($_SESSION['flash_type'], $_SESSION['flash_message']);
    return $flash;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function requireMethod(string $method): void
{
    if (strcasecmp($_SERVER['REQUEST_METHOD'] ?? 'GET', $method) !== 0) {
        http_response_code(405);
        header('Allow: ' . strtoupper($method));
        exit('Method Not Allowed');
    }
}

function requirePostRequest(): void
{
    requireMethod('POST');
}

function requireClientLogin(): void
{
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
        $target = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';
        header('Location: ' . url('/login.php?redirect=' . $target), true, 303);
        exit;
    }
}

function requireAdminLogin(): void
{
    if (empty($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . url('/admin/login.php'), true, 303);
        exit;
    }
}

function moneyFormat(float $amount): string
{
    return 'INR ' . number_format($amount, 0, '.', ',');
}

function indianPhoneValid(string $phone): bool
{
    return (bool) preg_match('/^[6-9]\d{9}$/', $phone);
}

function passwordStrong(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/\d/', $password);
}

function normalizeAmenities($value): array
{
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    return is_array($value) ? array_values($value) : [];
}

function normalizeCommaList(?string $value): array
{
    if (!$value) {
        return [];
    }

    $items = array_map('trim', explode(',', $value));
    return array_values(array_filter($items, static fn($item) => $item !== ''));
}

function selectedValue(string $needle, ?string $haystack): string
{
    return $needle === $haystack ? 'selected' : '';
}

function checkedValue(string $needle, ?string $haystack): string
{
    return $needle === $haystack ? 'checked' : '';
}

function decodeJsonArray($value): array
{
    if (is_array($value)) {
        return array_values($value);
    }

    if (!is_string($value) || $value === '') {
        return [];
    }

    $decoded = json_decode($value, true);
    return is_array($decoded) ? array_values($decoded) : [];
}

function allowedValue(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function propertyMatchesPreferences(array $property, array $preferences): bool
{
    if (!$preferences) {
        return true;
    }

    if (!empty($preferences['transaction_type']) && $preferences['transaction_type'] !== 'any' && $property['transaction_type'] !== $preferences['transaction_type']) {
        return false;
    }

    if (!empty($preferences['bhk_type']) && strcasecmp((string) $property['bhk_type'], (string) $preferences['bhk_type']) !== 0) {
        return false;
    }

    if (!empty($preferences['property_type']) && $preferences['property_type'] !== 'any' && $property['society_type'] !== $preferences['property_type']) {
        return false;
    }

    if (!empty($preferences['min_budget']) && (float) $property['price'] < (float) $preferences['min_budget']) {
        return false;
    }

    if (!empty($preferences['max_budget']) && (float) $property['price'] > (float) $preferences['max_budget']) {
        return false;
    }

    if (!empty($preferences['preferred_areas'])) {
        $areas = array_map('trim', explode(',', (string) $preferences['preferred_areas']));
        $match = false;
        foreach ($areas as $area) {
            if ($area !== '' && stripos((string) $property['area_locality'], $area) !== false) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            return false;
        }
    }

    if (!empty($preferences['amenities_needed'])) {
        $needed = decodeJsonArray($preferences['amenities_needed']);
        $have = decodeJsonArray($property['amenities'] ?? '[]');
        if ($needed && count(array_intersect($needed, $have)) < count($needed)) {
            return false;
        }
    }

    return true;
}

function firstImageForProperty(PDO $pdo, array $property): string
{
    $stmt = $pdo->prepare('SELECT image_path FROM property_images WHERE property_id = ? ORDER BY sort_order ASC, image_id ASC LIMIT 1');
    $stmt->execute([(int) $property['property_id']]);
    $image = $stmt->fetchColumn();
    if (is_string($image) && $image !== '') {
        return url('/' . ltrim($image, '/'));
    }

    $primary = $property['primary_image'] ?? 'assets/images/default-property.svg';
    return url('/' . ltrim((string) $primary, '/'));
}

function propertyImageGallery(PDO $pdo, int $propertyId): array
{
    $stmt = $pdo->prepare('SELECT image_path FROM property_images WHERE property_id = ? ORDER BY sort_order ASC, image_id ASC');
    $stmt->execute([$propertyId]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return array_values(array_filter(array_map(static fn($image) => is_string($image) ? url('/' . ltrim($image, '/')) : '', $images)));
}
