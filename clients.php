<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = 'HomeFinder Admin | Clients';
$extraStyles = [asset('/css/admin.css')];

requireAdminLogin();
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
$search = trim((string) ($_GET['q'] ?? ''));
$where = '1=1';
$params = [];

if ($search !== '') {
    $where = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $params = [
        '%' . $search . '%',
        '%' . $search . '%',
        '%' . $search . '%',
    ];
}

$stmt = $pdo->prepare(
    'SELECT u.*, (SELECT COUNT(*) FROM interests i WHERE i.user_id = u.user_id) AS total_interests
     FROM users u
     WHERE ' . $where . '
     ORDER BY u.created_at DESC'
);
$stmt->execute($params);
$clients = $stmt->fetchAll();
?>
<section class="section admin-shell">
    <div class="container admin-grid">
        <aside class="panel admin-sidebar">
            <h1>Clients</h1>
            <a href="<?= url('/admin/dashboard.php') ?>">Dashboard</a>
            <a href="<?= url('/admin/properties.php') ?>">Manage Properties</a>
            <a href="<?= url('/admin/interests.php') ?>">Interest Requests</a>
        </aside>

        <div class="admin-main">
            <div class="panel">
                <div class="panel-header">
                    <h2>Registered Clients</h2>
                </div>

                <form class="inline-form" method="get">
                    <input class="form-input" type="text" name="q" placeholder="Search clients" value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered</th>
                                <th>Interests</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $row) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                                    <td><?= (int) $row['total_interests'] ?></td>
                                    <td><a href="<?= url('/admin/client-view.php?user_id=' . (int) $row['user_id']) ?>">View Profile</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
