<?php
require_once __DIR__ . '/init.php';

$pageTitle = $pageTitle ?? 'HomeFinder';
$bodyClass = $bodyClass ?? '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isClient = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'client');
$isAdmin = !empty($_SESSION['admin_id']) && (($_SESSION['role'] ?? '') === 'admin');
$flash = flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HomeFinder - Real estate assistance portal">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('/css/style.css') ?>">
    <?php if (!empty($extraStyles)) : ?>
        <?php foreach ((array) $extraStyles as $style) : ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<header class="site-header">
    <div class="container nav-shell">
        <a class="brand" href="<?= url('/index.php') ?>">
            <span class="brand-mark">HF</span>
            <span class="brand-text">HomeFinder</span>
        </a>
        <button class="hamburger" type="button" aria-label="Toggle navigation" aria-expanded="false" data-nav-toggle>Menu</button>
        <nav class="nav-links" data-nav-links>
            <a href="<?= url('/index.php') ?>">Home</a>
            <a href="<?= url('/properties.php') ?>">Properties</a>
            <a href="<?= url('/index.php#how-it-works') ?>">How it Works</a>
            <?php if ($isClient) : ?>
                <a href="<?= url('/dashboard.php') ?>">Dashboard</a>
                <a href="<?= url('/logout.php') ?>">Logout</a>
            <?php elseif ($isAdmin) : ?>
                <a href="<?= url('/admin/dashboard.php') ?>">Admin</a>
                <a href="<?= url('/logout.php?admin=1') ?>">Logout</a>
            <?php else : ?>
                <a href="<?= url('/login.php') ?>">Login</a>
                <a class="nav-cta" href="<?= url('/register.php') ?>">Sign Up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if ($flash) : ?>
    <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
        <div class="container"><?= htmlspecialchars($flash['message']) ?></div>
    </div>
<?php endif; ?>
<main id="main-content">

