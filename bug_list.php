<?php
require_once __DIR__ . '/db.php'; require_once __DIR__ . '/auth.php';
require_login(); $user = current_user();
$status = $_GET['status'] ?? '';
$sql = "SELECT b.*, (SELECT COUNT(*) FROM comments c WHERE c.bug_id = b.id) AS cnt, (SELECT created_at FROM bug_history h WHERE h.bug_id = b.id ORDER BY created_at DESC LIMIT 1) AS last_change FROM bugs b";
$params = [];
if ($status) { $sql .= " WHERE b.status = ?"; $params[] = $status; }
$sql .= " ORDER BY b.id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $bugs = $stmt->fetchAll();
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Список</title><link rel="stylesheet" href="style.css"></head><body><div class="container">
<div class="topbar"><span><?=htmlspecialchars($user['login'])?></span><div class="nav-links"><a href="bug_list.php">Главная</a><a href="bugs_search.php">Поиск</a><a href="dashboard.php">Аналитика</a><a href="logout.php">Выход</a></div></div>
<form method="GET" class="filter-form"><select name="status" onchange="this.form.submit()"><option value="">Все статусы</option><?php foreach(['New','Review','Confirmed','In Progress','Fixed','Closed','Rejected'] as $s) echo "<option ".($status===$s?'selected':'').">$s</option>"; ?></select></form>
<table><tr><th>ID</th><th>Заголовок</th><th>Статус</th><th>💬</th><th>Изменен</th><th></th></tr>
<?php foreach($bugs as $b): ?><tr><td><?=$b['id']?></td><td><?=htmlspecialchars($b['title'])?></td><td><?=htmlspecialchars($b['status'])?></td><td><?=$b['cnt']?></td><td><?=$b['last_change']?:'—'?></td><td><a href="bug_view.php?id=<?=$b['id']?>">Открыть</a></td></tr><?php endforeach; ?>
</table></div></body></html>