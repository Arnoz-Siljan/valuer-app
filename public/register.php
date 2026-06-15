<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    header('Location: /valuer-app/public/dashboard.php');
    exit;
}

$errors = [];
$values = ['ime' => '', 'priimek' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime     = trim($_POST['ime'] ?? '');
    $priimek = trim($_POST['priimek'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $geslo   = $_POST['geslo'] ?? '';
    $geslo2  = $_POST['geslo2'] ?? '';

    $values = compact('ime', 'priimek', 'email');

    if ($ime === '')         $errors['ime']     = 'Ime je obvezno.';
    if ($priimek === '')     $errors['priimek'] = 'Priimek je obvezen.';
    if ($email === '')       $errors['email']   = 'E-poštni naslov je obvezen.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
                             $errors['email']   = 'E-poštni naslov ni veljaven.';
    if (strlen($geslo) < 8) $errors['geslo']   = 'Geslo mora imeti vsaj 8 znakov.';
    elseif ($geslo !== $geslo2)
                             $errors['geslo2']  = 'Gesli se ne ujemata.';

    if (empty($errors)) {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Ta e-poštni naslov je že registriran.';
        } else {
            $hash = password_hash($geslo, PASSWORD_BCRYPT);
            $ins  = $pdo->prepare('INSERT INTO users (ime, priimek, email, geslo) VALUES (?, ?, ?, ?)');
            $ins->execute([$ime, $priimek, $email, $hash]);

            setFlash('success', 'Registracija uspešna. Prijavite se.');
            header('Location: /valuer-app/public/login.php');
            exit;
        }
    }
}

$pageTitle = 'Registracija — Valuer.si';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="auth-card">
    <h1>Registracija</h1>
    <form method="post" novalidate id="register-form">
        <div class="form-group <?= isset($errors['ime']) ? 'has-error' : '' ?>">
            <label for="ime">Ime</label>
            <input type="text" id="ime" name="ime" value="<?= htmlspecialchars($values['ime'], ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['ime'])): ?>
                <span class="error-msg"><?= htmlspecialchars($errors['ime'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group <?= isset($errors['priimek']) ? 'has-error' : '' ?>">
            <label for="priimek">Priimek</label>
            <input type="text" id="priimek" name="priimek" value="<?= htmlspecialchars($values['priimek'], ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['priimek'])): ?>
                <span class="error-msg"><?= htmlspecialchars($errors['priimek'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
            <label for="email">E-poštni naslov</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['email'])): ?>
                <span class="error-msg"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group <?= isset($errors['geslo']) ? 'has-error' : '' ?>">
            <label for="geslo">Geslo</label>
            <input type="password" id="geslo" name="geslo" required minlength="8">
            <?php if (isset($errors['geslo'])): ?>
                <span class="error-msg"><?= htmlspecialchars($errors['geslo'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group <?= isset($errors['geslo2']) ? 'has-error' : '' ?>">
            <label for="geslo2">Potrdi geslo</label>
            <input type="password" id="geslo2" name="geslo2" required minlength="8">
            <?php if (isset($errors['geslo2'])): ?>
                <span class="error-msg"><?= htmlspecialchars($errors['geslo2'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Registriraj se</button>
    </form>
    <p class="auth-link">Že imate račun? <a href="/valuer-app/public/login.php">Prijavite se</a></p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
