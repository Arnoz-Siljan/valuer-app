<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$NAMEN_OPTIONS = [
    'zavarovano posojanje',
    'sodni postopek',
    'stečajni postopek',
    'računovodsko poročanje',
    'davčni postopek',
    'poslovna odločitev naročnika',
];
$PODLAGA_OPTIONS = [
    'tržna vrednost',
    'likvidacijska vrednost',
    'tržna najemnina',
    'pravična vrednost',
];
$PREMISA_OPTIONS = [
    'sedanja ali obstoječa uporaba',
    'najgospodarnejša uporaba',
    'redna likvidacija',
];

$errors = [];
$values = [
    'naziv_narocnika'  => '',
    'naslov_narocnika' => '',
    'namen_cenitve'    => '',
    'podlaga_vrednosti'=> '',
    'premisa_vrednosti'=> '',
    'prvi_ogled'       => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['naziv_narocnika']   = trim($_POST['naziv_narocnika'] ?? '');
    $values['naslov_narocnika']  = trim($_POST['naslov_narocnika'] ?? '');
    $values['namen_cenitve']     = $_POST['namen_cenitve'] ?? '';
    $values['podlaga_vrednosti'] = $_POST['podlaga_vrednosti'] ?? '';
    $values['premisa_vrednosti'] = $_POST['premisa_vrednosti'] ?? '';
    $_datum = trim($_POST['prvi_ogled_datum'] ?? '');
    $_cas   = trim($_POST['prvi_ogled_cas']   ?? '');
    $values['prvi_ogled'] = ($_datum !== '' && $_cas !== '') ? $_datum . 'T' . $_cas : '';

    if ($values['naziv_narocnika'] === '')
        $errors['naziv_narocnika']  = 'Naziv naročnika je obvezen.';
    if ($values['naslov_narocnika'] === '')
        $errors['naslov_narocnika'] = 'Naslov naročnika je obvezen.';
    if (!in_array($values['namen_cenitve'], $NAMEN_OPTIONS, true))
        $errors['namen_cenitve']    = 'Izberite veljavni namen cenitve.';
    if (!in_array($values['podlaga_vrednosti'], $PODLAGA_OPTIONS, true))
        $errors['podlaga_vrednosti']= 'Izberite veljavno podlago vrednosti.';
    if (!in_array($values['premisa_vrednosti'], $PREMISA_OPTIONS, true))
        $errors['premisa_vrednosti']= 'Izberite veljavno premiso vrednosti.';
    if ($values['prvi_ogled'] === '')
        $errors['prvi_ogled']       = 'Datum in čas prvega ogleda sta obvezna.';
    elseif (!strtotime($values['prvi_ogled']))
        $errors['prvi_ogled']       = 'Neveljavni datum ali čas.';

    if (empty($errors)) {
        $pdo  = getPDO();
        $stmt = $pdo->prepare(
            'INSERT INTO valuations
             (user_id, naziv_narocnika, naslov_narocnika, namen_cenitve, podlaga_vrednosti, premisa_vrednosti, prvi_ogled)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            currentUserId(),
            $values['naziv_narocnika'],
            $values['naslov_narocnika'],
            $values['namen_cenitve'],
            $values['podlaga_vrednosti'],
            $values['premisa_vrednosti'],
            date('Y-m-d H:i:s', strtotime($values['prvi_ogled'])),
        ]);

        setFlash('success', 'Cenitev je bila uspešno dodana.');
        header('Location: /valuer-app/public/dashboard.php');
        exit;
    }
}

$pageTitle = 'Nova cenitev — Valuer.si';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-card">
    <h1>Nova cenitev</h1>
    <form method="post" novalidate id="valuation-form">
        <?php require __DIR__ . '/../includes/valuation_fields.php'; ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Shrani</button>
            <a href="/valuer-app/public/dashboard.php" class="btn btn-secondary">Prekliči</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
