<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Valuer.si', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/valuer-app/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="logo" href="/valuer-app/public/dashboard.php">Valuer.si</a>
        <nav>
            <?php if (isLoggedIn()): ?>
                <span class="nav-user">Pozdravljeni, <?= currentUserName() ?></span>
                <a href="/valuer-app/public/valuation_add.php">+ Nova cenitev</a>
                <a href="/valuer-app/public/logout.php">Odjava</a>
            <?php else: ?>
                <a href="/valuer-app/public/login.php">Prijava</a>
                <a href="/valuer-app/public/register.php">Registracija</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
<?php
$flash = getFlash();
if ($flash): ?>
    <div class="flash flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>
