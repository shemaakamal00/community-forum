<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();

$userId = $_SESSION['user_id'];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $groupId = (int)($_POST['group_id'] ?? 0);

    if ($groupId > 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO memberships (user_id, group_id, role, status)
             VALUES (?, ?, 'member', 'pending')
             ON DUPLICATE KEY UPDATE status = 'pending'"
        );
        $stmt->execute([$userId, $groupId]);
        $notice = 'Din ansökan har skickats!';
    }
}

$stmt = $pdo->prepare(
    "SELECT g.id, g.name, g.description, m.role
     FROM `groups` g
     JOIN memberships m ON m.group_id = g.id
     WHERE m.user_id = ? AND m.status = 'approved'
     ORDER BY g.name"
);
$stmt->execute([$userId]);
$myGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT g.id, g.name FROM `groups` g
     JOIN memberships m ON m.group_id = g.id
     WHERE m.user_id = ? AND m.status = 'pending'
     ORDER BY g.name"
);
$stmt->execute([$userId]);
$pendingGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT g.id, g.name, g.description FROM `groups` g
     WHERE g.id NOT IN (
         SELECT group_id FROM memberships WHERE user_id = ?
     )
     ORDER BY g.name"
);
$stmt->execute([$userId]);
$otherGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sv">
<head><meta charset="utf-8"><title>Grupper</title></head>
<body>
    <?= nav() ?>
    <h1>Grupper</h1>

    <?php if ($notice): ?><p style="color:green;"><?= e($notice) ?></p><?php endif; ?>

    <h2>Mina grupper</h2>
    <?php if (!$myGroups): ?>
        <p>Du är inte medlem i någon grupp än.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($myGroups as $g): ?>
            <li>
                <a href="group.php?id=<?= (int)$g['id'] ?>"><?= e($g['name']) ?></a>
                <?php if ($g['role'] === 'admin'): ?>
                    <strong>(admin)</strong>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Andra grupper</h2>
    <?php if (!$otherGroups && !$pendingGroups): ?>
        <p>Det finns inga andra grupper just nu.</p>
    <?php endif; ?>

    <ul>
    <?php foreach ($otherGroups as $g): ?>
        <li>
            <strong><?= e($g['name']) ?></strong>
            — <?= e($g['description'] ?? '') ?>
            <form method="post" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="group_id" value="<?= (int)$g['id'] ?>">
                <button type="submit">Ansök om att gå med</button>
            </form>
        </li>
    <?php endforeach; ?>

    <?php foreach ($pendingGroups as $g): ?>
        <li><strong><?= e($g['name']) ?></strong> — <em>ansökan väntar på godkännande</em></li>
    <?php endforeach; ?>
    </ul>
</body>
</html>