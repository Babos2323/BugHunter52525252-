<?php
require_once __DIR__ . '/db.php'; require_once __DIR__ . '/auth.php'; require_login();
$q = trim($_GET['q'] ?? ''); $params = []; $where = [];
if ($q) { $where[] = "(b.title LIKE ? OR b.description LIKE ? OR b.steps LIKE ? OR u.email LIKE ?)"; $escaped = '%' . str_replace(['%','_'],['\%','\_'],$q) . '%'; $params = array_merge($params, [$escaped,$escaped,$escaped,$escaped]); }
$sort = in_array($_GET['sort']??'', ['date','priority','status']) ? $_GET['sort'] : 'date';
$sortSql = $sort === 'priority' ? "CASE b.priority WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Medium' THEN 3 ELSE 4 END" : ($sort === 'status' ? 'b.status' : 'b.created_at');
$dir = ($_GET['dir'] ?? '') === 'asc' ? 'ASC' : 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page - 1) * 10;
$sql = "SELECT b.*, u.login as author FROM bugs b LEFT JOIN users u ON u.id=b.author_id" . ($where ? " WHERE ".implode(' AND ',$where) : "") . " ORDER BY $sortSql $dir LIMIT 10 OFFSET $offset";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $bugs = $stmt->fetchAll();
$total = $pdo->prepare("SELECT COUNT(*) FROM bugs b LEFT JOIN users u ON u.id=b.author_id" . ($where ? " WHERE ".implode(' AND ',$where) : "")); $total->execute($params); $pages = ceil($total->fetchColumn()/10);
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Поиск</title><link rel="stylesheet" href="style.css"></head><body><div class="container">
<a href="bug_list.php">← Назад</a><h1>Поиск</h1>
<form method="GET" class="filter-form"><input name="q" value="<?=htmlspecialchars($q)?>" placeholder="Название, описание, шаги, email"><button>Найти</button></form>
<table><tr><th>ID</th><th>Заголовок</th><th>Автор</th><th>Приоритет</th><th>Статус</th></tr>
<?php foreach($bugs as $b) echo "<tr><td>{$b['id']}</td><td>".htmlspecialchars($b['title'])."</td><td>{$b['author']}</td><td>{$b['priority']}</td><td>{$b['status']}</td></tr>"; ?>
</table><p>Стр. <?=$page?> из <?=$pages?></p></div></body></html>