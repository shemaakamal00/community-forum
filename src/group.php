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

$isAdmin = ($membership['role'] === 'admin');
$notice  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if (!$isAdmin) {
        http_response_code(403);
        exit('Endast administratörer får hantera ansökningar.');
    }

    $applicantId = (int)($_POST['applicant_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($applicantId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

        $stmt = $pdo->prepare(
            "UPDATE memberships
             SET status = ?
             WHERE user_id = ? AND group_id = ? AND status = 'pending'"
        );
        $stmt->execute([$newStatus, $applicantId, $groupId]);

        $notice = ($action === 'approve')
            ? 'Ansökan godkänd – användaren är nu medlem.'
            : 'Ansökan nekad.';
    }
}

$pending = [];
if ($isAdmin) {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.first_name, u.last_name, u.email
         FROM memberships m
         JOIN users u ON u.id = m.user_id
         WHERE m.group_id = ? AND m.status = 'pending'
         ORDER BY m.created_at"
    );
    $stmt->execute([$groupId]);
    $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <?php if ($notice): ?>
        <p style="color:green;"><?= e($notice) ?></p>
    <?php endif; ?>

    <?php if($isAdmin): ?>
        <h2>Väntande ansökningar</h2>
        <?php if (!$pending): ?>
            <p>Inga väntande ansökningar just nu.</p>
        <?php else: ?>
            <ul>
            <?php foreach ($pending as $p): ?>
                <li>
                    <?= e($p['first_name'] . ' ' . $p['last_name']) ?>
                    (<?= e($p['email']) ?>)

                    <form method="post" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="applicant_id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" name="action" value="approve">Godkänn</button>
                        <button type="submit" name="action" value="reject">Neka</button>
                    </form>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <p><em>Här kommer diskussioner snart.</em></p>
</body>
</html>