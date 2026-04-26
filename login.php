<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Login';
$extraScripts = [asset('/js/validation.js')];
require_once __DIR__ . '/includes/header.php';
?>
<section class="section auth-section">
    <div class="container auth-shell">
        <form class="panel auth-card" action="<?= url('/php/auth.php?action=login') ?>" method="post" data-validate>
            <h1>Client Login</h1>
            <p>Continue to your dashboard and saved preferences.</p>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label>Email Address <input class="form-input" type="email" name="email" required></label>
            <label>Password <input class="form-input" type="password" name="password" required minlength="8"></label>
            <button class="btn btn-primary" type="submit">Login</button>
            <p class="muted"><a href="<?= url('/register.php') ?>">Create a new account</a> | <a href="<?= url('/admin/login.php') ?>">Admin login</a></p>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
