<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_login();

$total = $pdo->query("SELECT COUNT(*) FROM bugs")->fetchColumn();
$closed = $pdo->query("SELECT COUNT(*) FROM bugs WHERE status='Closed'")->fetchColumn();
$rej = $pdo->query("SELECT COUNT(*) FROM bugs WHERE status='Rejected'")->fetchColumn();
$open = $total - $closed - $rej;
$crit = $pdo->query("SELECT COUNT(*) FROM bugs WHERE priority='Critical'")->fetchColumn();
$stats = $pdo->query("SELECT status, COUNT(*) as c FROM bugs GROUP BY status")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Аналитика</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="topbar">
        <a href="bug_list.php">← Назад</a>
        <div class="nav-links"><a href="logout.php">Выйти</a></div>
    </div>
    
    <h1>Аналитика</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Всего</h3>
            <div class="stat-number"><?=$total?></div>
        </div>
        <div class="stat-card">
            <h3>Открыто</h3>
            <div class="stat-number" style="color: #b45309;"><?=$open?></div>
        </div>
        <div class="stat-card">
            <h3>Закрыто</h3>
            <div class="stat-number" style="color: #166534;"><?=$closed?></div>
        </div>
        <div class="stat-card">
            <h3>Critical</h3>
            <div class="stat-number" style="color: #991b1b;"><?=$crit?></div>
        </div>
    </div>

    <h2>Статусы</h2>
    <div style="display: flex; flex-direction: column; gap: 12px;">
    <?php foreach($stats as $s): 
        $width = $total > 0 ? round(($s['c'] / $total) * 100) : 0;
    ?>
        <div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                <span><?=htmlspecialchars($s['status'])?></span>
                <b><?=$s['c']?></b>
            </div>
            <div style="background: var(--border); border-radius: 4px; height: 8px; overflow: hidden;">
                <div style="background: var(--primary); height: 100%; width: <?=$width?>%; border-radius: 4px;"></div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
</body>
</html>