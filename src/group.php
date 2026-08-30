<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();

$userId = $_SESSION['user_id'];
$groupId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT m.role, g.name, g.description
    FROM memberships m
    JOIN `groups` g ON g.id = m.group_id
    WHERE m.user_id = ? AND m.group_id = ? AND m.status = 'approved'"
);
$stmt->execute([$userId, $groupId]);
$membership = $stmt->fetch(PDO:: FETCH_ASSOC);

if(!$membership) {
    http_response_code(403);
    echo nav();
    echo "<p>Du har inte tillgång till den här gruppen.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head><meta charset="utf-8"><title><?= e($membership['name']) ?></title></head>
<body>
    <?= nav() ?>
    <h1><?= e($membership['name']) ?></h1>
    <p><?= e($membership['description'] ?? '') ?></p>
    <p>Din roll: <strong><?= e($membership['role']) ?></strong></p>
    <p><em>Här kommer diskussioner och ansökningar snart.</em></p>
</body>
</html>