<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    header('Location: /valuer-app/public/dashboard.php');
    exit;
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = trim($_POST['email'] ?? '');
    $geslo  = $_POST['geslo'] ?? '';

    if ($email === '')  $errors['email'] = 'Vnesite e-poštni naslov.';
    if ($geslo === '')  $errors['geslo'] = 'Vnesite geslo.';

    if (empty($errors)) {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('SELECT id, ime, geslo FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($geslo, $user['geslo'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['ime'];

            setFlash('success', 'Prijava uspešna. Dobrodošli!');
            header('Location: /valuer-app/public/dashboard.php');
            exit;
        } else {
            $errors['general'] = 'Napačen e-poštni naslov ali geslo.';
        }
    }
}

$pageTitle = 'Prijava — Valuer.si';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="auth-card">
    <h1>Prijava</h1>
    <?php if (isset($errors['general'])): ?>
        <div class="flash flash-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" novalidate id="login-form">
        <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
            <label for="email">E-poštni naslov</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            <?php if (isset($errors['email'])): ?>
                <span class="error-msg"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group <?= isset($errors['geslo']) ? 'has-error' : '' ?>">
            <label for="geslo">Geslo</label>
            <input type="password" id="geslo" name="geslo" required>
            <?php if (isset($errors['geslo'])): ?>
                <span class="error-msg"><?= htmlspecialchars($errors['geslo'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Prijava</button>
    </form>
    <p class="auth-link">Nimate računa? <a href="/valuer-app/public/register.php">Registrirajte se</a></p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
