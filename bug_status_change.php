<?php
require_once __DIR__ . '/db.php'; require_once __DIR__ . '/auth.php';
require_login();
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf_token'] ?? '')) exit('CSRF error');
$id = filter_input(INPUT_POST, 'bug_id', FILTER_VALIDATE_INT);
$newStatus = $_POST['new_status'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM bugs WHERE id = ?'); $stmt->execute([$id]); $bug = $stmt->fetch();
if (!$bug) exit('Не найдено');
$allowed = ['New'=>['Review'],'Review'=>['Confirmed','Rejected'],'Confirmed'=>['In Progress'],'In Progress'=>['Fixed'],'Fixed'=>['Closed','In Progress']];
$roleMatrix = ['Tester'=>['New'=>['Review'],'Review'=>['Confirmed','Rejected'],'Fixed'=>['Closed','In Progress']],'Developer'=>['Confirmed'=>['In Progress'],'In Progress'=>['Fixed']],'Admin'=>$allowed];
if ($_SESSION['role'] !== 'Admin' && !in_array($newStatus, $roleMatrix[$_SESSION['role']][$bug['status']] ?? [], true)) exit('403 Forbidden');
try {
    $pdo->beginTransaction();
    $upd = $pdo->prepare('UPDATE bugs SET status = ? WHERE id = ? AND status = ?');
    $upd->execute([$newStatus, $id, $bug['status']]);
    if ($upd->rowCount() === 0) { $pdo->rollBack(); exit('Статус уже изменен'); }
    $hist = $pdo->prepare('INSERT INTO bug_history (bug_id, user_id, old_status, new_status) VALUES (?, ?, ?, ?)');
    $hist->execute([$id, $_SESSION['user_id'], $bug['status'], $newStatus]);
    $pdo->commit();
} catch (Throwable $e) { $pdo->rollBack(); exit('Ошибка БД'); }
header('Location: bug_view.php?id=' . $id); exit;