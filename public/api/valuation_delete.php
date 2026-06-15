<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Niste prijavljeni.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda ni dovoljena.']);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neveljaven ID.']);
    exit;
}

$pdo  = getPDO();
$stmt = $pdo->prepare('DELETE FROM valuations WHERE id = ? AND user_id = ?');
$stmt->execute([$id, currentUserId()]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Cenitev ni bila najdena.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Cenitev je bila izbrisana.']);
