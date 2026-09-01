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

$pageTitle = 'Grupper';
require 'header.php';
?>
    <h1>Grupper</h1>

    <?php if ($notice): ?><p class="notice""><?= e($notice) ?></p><?php endif; ?>

    <h2>Mina grupper</h2>
    <?php if (!$myGroups): ?>
        <p>Du är inte medlem i någon grupp än.</p>
    <?php else: ?>
        <ul class="list">
        <?php foreach ($myGroups as $g): ?>
            <li>
                <a href="group.php?id=<?= (int)$g['id'] ?>"><?= e($g['name']) ?></a>
                <?php if ($g['role'] === 'admin'): ?>
                    <span class="badge">(admin)</span >
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Andra grupper</h2>
    <?php if (!$otherGroups && !$pendingGroups): ?>
        <p>Det finns inga andra grupper just nu.</p>
    <?php else: ?>

    <ul class="list">
    <?php foreach ($otherGroups as $g): ?>
        <li>
            <strong><?= e($g['name']) ?></strong>
            <?php if (!empty($g['description'])): ?>
                <div class="meta"><?=  e($g['description']) ?></div>
            <?php endif; ?>
            <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="group_id" value="<?= (int)$g['id'] ?>">
                <button type="submit">Ansök om att gå med</button>
            </form>
        </li>
    <?php endforeach; ?>

    <?php foreach ($pendingGroups as $g): ?>
        <li>
            <strong><?= e($g['name']) ?></strong>
            <div class="meta">Ansökan väntar på godkännande</div>
        </li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>
<?php require 'footer.php'; ?>