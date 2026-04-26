<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Register';
$extraScripts = [asset('/js/validation.js')];
require_once __DIR__ . '/includes/header.php';
?>
<section class="section auth-section">
    <div class="container auth-shell">
        <form class="panel auth-card" action="<?= url('/php/auth.php?action=register') ?>" method="post" data-validate>
            <h1>Create Account</h1>
            <p>Unlock full listings, preferences, and the interested workflow.</p>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label>Full Name <input class="form-input" type="text" name="full_name" required minlength="2" maxlength="80"></label>
            <label>Email Address <input class="form-input" type="email" name="email" required></label>
            <label>Phone Number <input class="form-input" type="tel" name="phone" required pattern="^[6-9]\d{9}$"></label>
            <label>Password <input class="form-input" type="password" name="password" required minlength="8"></label>
            <label>Confirm Password <input class="form-input" type="password" name="confirm_password" required minlength="8"></label>
            <button class="btn btn-primary" type="submit">Create Account</button>
            <p class="muted">Already have an account? <a href="<?= url('/login.php') ?>">Login here</a></p>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
