        </main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand footer-brand" href="<?= url('/index.php') ?>">
                <span class="brand-mark">HF</span>
                <span class="brand-text">HomeFinder</span>
            </a>
            <p>Find curated homes in Pune with a simple search, trusted listings, and a controlled meeting flow.</p>
        </div>
        <div>
            <h4>Explore</h4>
            <a href="<?= url('/properties.php') ?>">Browse Properties</a><br>
            <a href="<?= url('/register.php') ?>">Create Account</a><br>
            <a href="<?= url('/login.php') ?>">Client Login</a>
        </div>
        <div>
            <h4>Contact</h4>
            <p>support@homefinder.local</p>
            <p>Pune, Maharashtra</p>
        </div>
    </div>
    <div class="container footer-bottom">© <?= date('Y') ?> HomeFinder. All rights reserved.</div>
</footer>
<script src="<?= asset('/js/main.js') ?>"></script>
<?php if (!empty($extraScripts)) : ?>
    <?php foreach ((array) $extraScripts as $script) : ?>
        <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>

