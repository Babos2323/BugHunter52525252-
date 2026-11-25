<?php
require_once __DIR__ . '/db.php'; require_once __DIR__ . '/auth.php';
require_login(); $user = current_user();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); if (!$id) exit('Ошибка');
$stmt = $pdo->prepare('SELECT * FROM bugs WHERE id = ?'); $stmt->execute([$id]); $bug = $stmt->fetch(); if (!$bug) exit('404');
$history = $pdo->prepare('SELECT h.*, u.login FROM bug_history h JOIN users u ON u.id=h.user_id WHERE h.bug_id=? ORDER BY h.id ASC'); $history->execute([$id]); $hist = $history->fetchAll();
$comments = $pdo->prepare('SELECT c.*, u.login FROM comments c JOIN users u ON u.id=c.user_id WHERE c.bug_id=? ORDER BY c.id ASC'); $comments->execute([$id]); $comm = $comments->fetchAll();
$trans = ['New'=>['Review'],'Review'=>['Confirmed','Rejected'],'Confirmed'=>['In Progress'],'In Progress'=>['Fixed'],'Fixed'=>['Closed','In Progress']];
$matrix = ['Tester'=>['New'=>['Review'],'Review'=>['Confirmed','Rejected'],'Fixed'=>['Closed','In Progress']],'Developer'=>['Confirmed'=>['In Progress'],'In Progress'=>['Fixed']],'Admin'=>$trans];
$allowed = $_SESSION['role']==='Admin' ? ($trans[$bug['status']]??[]) : ($matrix[$_SESSION['role']][$bug['status']]??[]);
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32)); $csrf = $_SESSION['csrf'];
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Баг #<?=$id?></title><link rel="stylesheet" href="style.css"></head><body><div class="container">
<a href="bug_list.php">← Назад</a><h1>#<?=$id?> <?=htmlspecialchars($bug['title'])?></h1><p>Статус: <b><?=$bug['status']?></b></p><p>Описание: <?=nl2br(htmlspecialchars($bug['description']))?></p>
<h2>История</h2><ul><?php foreach($hist as $h) echo "<li>{$h['old_status']} → {$h['new_status']} ({$h['login']})</li>"; ?></ul>
<h2>Комментарии</h2><?php foreach($comm as $c) echo "<div class='comment'><b>{$c['login']}</b>: ".nl2br(htmlspecialchars($c['text']))."</div>"; ?>
<form method="POST" action="comment_add.php"><input type="hidden" name="bug_id" value="<?=$id?>"><input type="hidden" name="csrf_token" value="<?=$csrf?>"><textarea name="text" required></textarea><button>Отправить</button></form>
<?php if($allowed): ?><h2>Смена статуса</h2><form method="POST" action="bug_status_change.php"><input type="hidden" name="bug_id" value="<?=$id?>"><input type="hidden" name="csrf_token" value="<?=$csrf?>"><select name="new_status"><?php foreach($allowed as $s) echo "<option>$s</option>"; ?></select><button>Изменить</button></form><?php endif; ?>
</div></body></html>