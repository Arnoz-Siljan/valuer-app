<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$pdo    = getPDO();
$userId = currentUserId();

// Optional search/filter
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int) ($_GET['stran'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$whereExtra = '';
$params     = [$userId];
if ($search !== '') {
    $whereExtra = ' AND (naziv_narocnika LIKE ? OR naslov_narocnika LIKE ?)';
    $params[]   = '%' . $search . '%';
    $params[]   = '%' . $search . '%';
}

$totalStmt = $pdo->prepare('SELECT COUNT(*) FROM valuations WHERE user_id = ?' . $whereExtra);
$totalStmt->execute($params);
$total = (int) $totalStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$paramsPage = array_merge($params, [$perPage, $offset]);
$stmt = $pdo->prepare(
    'SELECT * FROM valuations WHERE user_id = ?' . $whereExtra .
    ' ORDER BY prvi_ogled DESC LIMIT ? OFFSET ?'
);
$stmt->execute($paramsPage);
$valuations = $stmt->fetchAll();

$pageTitle = 'Pregled cenitiev — Valuer.si';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-header">
    <h1>Moje cenitve</h1>
    <a href="/valuer-app/public/valuation_add.php" class="btn btn-primary">+ Nova cenitev</a>
</div>

<form method="get" class="search-form" role="search">
    <input type="search" name="q" placeholder="Iskanje po naročniku ali naslovu…"
           value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn">Išči</button>
    <?php if ($search !== ''): ?>
        <a href="/valuer-app/public/dashboard.php" class="btn btn-secondary">Ponastavi</a>
    <?php endif; ?>
</form>

<?php if (empty($valuations)): ?>
    <p class="empty-state">Ni cenitiev.
        <?= $search !== '' ? 'Poskusite z drugim iskalnim izrazom.' : 'Dodajte svojo prvo cenitev!' ?>
    </p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Naročnik</th>
                    <th>Naslov</th>
                    <th>Namen</th>
                    <th>Podlaga vrednosti</th>
                    <th>Premisa vrednosti</th>
                    <th>Prvi ogled</th>
                    <th>Dejanja</th>
                </tr>
            </thead>
            <tbody id="valuations-tbody">
                <?php foreach ($valuations as $v): ?>
                    <tr id="row-<?= $v['id'] ?>">
                        <td><?= htmlspecialchars($v['naziv_narocnika'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($v['naslov_narocnika'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($v['namen_cenitve'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($v['podlaga_vrednosti'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($v['premisa_vrednosti'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($v['prvi_ogled'])), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="actions">
                            <a href="/valuer-app/public/valuation_edit.php?id=<?= $v['id'] ?>" class="btn btn-small">Uredi</a>
                            <button
                                class="btn btn-small btn-danger btn-delete"
                                data-id="<?= $v['id'] ?>"
                                data-name="<?= htmlspecialchars($v['naziv_narocnika'], ENT_QUOTES, 'UTF-8') ?>">
                                Izbriši
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?stran=<?= $i ?>&q=<?= urlencode($search) ?>"
                   class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
