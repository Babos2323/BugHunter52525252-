<?php
require_once __DIR__ . '/db.php'; require_once __DIR__ . '/auth.php';
require_login();
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf_token'] ?? '')) exit('CSRF error');
$id = filter_input(INPUT_POST, 'bug_id', FILTER_VALIDATE_INT);
$text = trim($_POST['text'] ?? '');
if (!$id || $text === '' || !preg_match('/\S/u', $text) || mb_strlen($text) > 2000) exit('Ошибка валидации');
$stmt = $pdo->prepare('INSERT INTO comments (bug_id, user_id, text) VALUES (?, ?, ?)');
$stmt->execute([$id, $_SESSION['user_id'], $text]);
header('Location: bug_view.php?id=' . $id); exit;