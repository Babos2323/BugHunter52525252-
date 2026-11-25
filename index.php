<?php
require_once __DIR__ . '/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ?');
    $stmt->execute([trim($_POST['login'] ?? '')]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id']; $_SESSION['role'] = $user['role']; $_SESSION['login'] = $user['login'];
        header('Location: bug_list.php'); exit;
    }
    $error = 'Неверный логин или пароль';
}
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Вход</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container"><h1>Вход</h1><?php if ($error) echo "<p class='error'>$error</p>"; ?>
<form method="POST"><label>Логин<br><input name="login" required></label><label>Пароль<br><input name="password" type="password" required></label><button>Войти</button></form>
<p class="hint">tester / 12345</p></div></body></html>