<?php
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: /valuer-app/public/dashboard.php');
} else {
    header('Location: /valuer-app/public/login.php');
}
exit;
