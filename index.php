<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'HomeFinder | Find Your Perfect Home in Pune';
$extraScripts = [asset('/js/validation.js')];
require_once __DIR__ . '/includes/header.php';
$pdo = getDBConnection();
$featured = $pdo->query('SELECT * FROM properties WHERE is_featured = 1 AND status = "active" ORDER BY created_at DESC LIMIT 3')->fetchAll();
if (!$featured) {
    $featured = $pdo->query('SELECT * FROM properties WHERE status = "active" ORDER BY created_at DESC LIMIT 3')->fetchAll();
}
?>
<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">Real Estate Assistance Portal</span>
            <h1>Find Your Perfect Home in Pune</h1>
            <p>Tell us what you need. We show you what matches.</p>
            <div class="hero-actions">
                <a class="btn btn-accent" href="#quick-form">Get Started - It's Free</a>
                <a class="btn btn-ghost" href="<?= url('/properties.php') ?>">Browse Properties</a>
            </div>
        </div>
        <div class="hero-card">
            <h3>Search made simple</h3>
            <p>Guest browsing, saved preferences, and a guided interest flow for serious buyers and tenants.</p>
            <ul class="check-list">
                <li>Curated matches</li>
                <li>Safe lead confirmation</li>
                <li>Admin-managed listings</li>
            </ul>
        </div>
    </div>
</section>

<section class="section stats-section">
    <div class="container stats-grid">
        <article class="stat-card"><strong data-counter data-target="500">0</strong><span>Clients Helped</span></article>
        <article class="stat-card"><strong data-counter data-target="200">0</strong><span>Properties Listed</span></article>
        <article class="stat-card"><strong data-counter data-target="50">0</strong><span>Localities Covered</span></article>
    </div>
</section>

<section id="how-it-works" class="section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">How it works</span>
            <h2>From search to site visit in three clear steps</h2>
        </div>
        <div class="feature-grid three-up">
            <article class="feature-card"><span class="step-number">1</span><h3>Tell us your requirement</h3><p>Fill the quick form with area, BHK, transaction type, and budget.</p></article>
            <article class="feature-card"><span class="step-number">2</span><h3>Browse matched properties</h3><p>See curated listings with limited previews as a guest, or full details after login.</p></article>
            <article class="feature-card"><span class="step-number">3</span><h3>Connect with the agent</h3><p>Confirm interest through the booking flow and let the admin arrange the meeting.</p></article>
        </div>
    </div>
</section>

<section id="quick-form" class="section section-alt">
    <div class="container split-layout">
        <div>
            <span class="eyebrow">Start your property search</span>
            <h2>Share a few details and we’ll show the best matches</h2>
            <p>Guest search is quick, private, and does not require registration.</p>
        </div>
        <form class="panel form-grid" action="<?= url('/php/requirements.php') ?>" method="post" data-validate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label>Full Name <input class="form-input" type="text" name="full_name" required minlength="2" maxlength="60"></label>
            <label>Phone Number <input class="form-input" type="tel" name="phone" required pattern="^[6-9]\d{9}$" placeholder="9876543210"></label>
            <label>Email Address <input class="form-input" type="email" name="email" placeholder="optional"></label>
            <label>City / Area <input class="form-input" type="text" name="area" list="areas" required><datalist id="areas"><option>Kothrud</option><option>Baner</option><option>Wakad</option><option>Aundh</option><option>Viman Nagar</option><option>Hadapsar</option></datalist></label>
            <fieldset>
                <legend>Transaction Type</legend>
                <label><input type="radio" name="transaction_type" value="rent" checked> Rent</label>
                <label><input type="radio" name="transaction_type" value="buy"> Buy</label>
            </fieldset>
            <label>BHK Type <select class="form-input" name="bhk_type"><option>1BHK</option><option selected>2BHK</option><option>3BHK</option><option>4BHK</option><option>Villa</option></select></label>
            <div class="two-col">
                <label>Min Budget (INR) <input class="form-input" type="number" name="min_budget" min="1" required></label>
                <label>Max Budget (INR) <input class="form-input" type="number" name="max_budget" min="1" required></label>
            </div>
            <button class="btn btn-primary" type="submit">Find Properties</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading"><span class="eyebrow">Featured</span><h2>Featured properties</h2></div>
        <div class="property-grid">
            <?php foreach ($featured as $property) : ?>
                <article class="property-card">
                    <img src="<?= htmlspecialchars(firstImageForProperty($pdo, $property)) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                    <div class="property-body">
                        <h3><?= htmlspecialchars($property['title']) ?></h3>
                        <p><?= htmlspecialchars($property['area_locality']) ?> • <?= htmlspecialchars($property['bhk_type']) ?></p>
                        <div class="property-meta"><strong><?= moneyFormat((float) $property['price']) ?></strong><a class="btn btn-small" href="<?= url('/property-detail.php?id=' . (int) $property['property_id']) ?>">View Details</a></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-heading"><span class="eyebrow">Why people trust us</span><h2>Testimonials</h2></div>
        <div class="feature-grid three-up">
            <article class="quote-card"><p>HomeFinder made it easy to shortlist real options without spam calls.</p><strong>Anita</strong><span>★★★★★</span></article>
            <article class="quote-card"><p>The preference filters are spot on. It saved me hours every week.</p><strong>Rohan</strong><span>★★★★★</span></article>
            <article class="quote-card"><p>As an admin, the lead flow is clean and genuinely actionable.</p><strong>Suresh</strong><span>★★★★★</span></article>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
