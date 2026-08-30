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
$membership = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$membership) {
    http_response_code(403);
    echo nav();
    echo "<p>Du har inte tillgång till den här gruppen.</p>";
    exit;
}

$isAdmin = ($membership['role'] === 'admin');
$notice  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'new_topic') {
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if ($title === '' || $body === '') {
            $notice = 'Både titel och innehåll måste fyllas i.';
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "INSERT INTO topics (group_id, title, created_by) VALUES (?, ?, ?)"
                );
                $stmt->execute([$groupId, $title, $userId]);
                $topicId = $pdo->lastInsertId();

                $stmt = $pdo->prepare(
                    "INSERT INTO posts (topic_id, user_id, body) VALUES (?, ?, ?)"
                );
                $stmt->execute([$topicId, $userId, $body]);

                $pdo->commit();
                header('Location: topic.php?id=' . $topicId);
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $notice = 'Kunde inte skapa ämnet, försök igen.';
            }
        }
    }

    elseif ($formType === 'application') {
        if (!$isAdmin) {
            http_response_code(403);
            exit('Endast administratörer får hantera ansökningar.');
        }

        $applicantId = (int)($_POST['applicant_id'] ?? 0);
        $action      = $_POST['action'] ?? '';

        if ($applicantId > 0 && in_array($action, ['approve', 'reject'], true)) {
            $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

            $stmt = $pdo->prepare(
                "UPDATE memberships SET status = ?
                 WHERE user_id = ? AND group_id = ? AND status = 'pending'"
            );
            $stmt->execute([$newStatus, $applicantId, $groupId]);

            $notice = ($action === 'approve')
                ? 'Ansökan godkänd! Användaren är nu medlem.'
                : 'Ansökan nekad.';
        }
    }

    elseif ($formType === 'role_change') {
        if (!$isAdmin) {
            http_response_code(403);
            exit('Endast administratörer får ändra roller.');
        }

        $targetId = (int)($_POST['target_id'] ?? 0);
        $newRole  = $_POST['new_role'] ?? '';

        if ($targetId > 0 && $targetId !== (int)$userId
            && in_array($newRole, ['member', 'admin'], true)) {

            $stmt = $pdo->prepare(
                "UPDATE memberships SET role = ?
                 WHERE user_id = ? AND group_id = ? AND status = 'approved'"
            );
            $stmt->execute([$newRole, $targetId, $groupId]);
            $notice = 'Rollen har uppdaterats!';
        }
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

$members = [];
if ($isAdmin) {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.first_name, u.last_name, m.role
         FROM memberships m
         JOIN users u ON u.id = m.user_id
         WHERE m.group_id = ? AND m.status = 'approved' AND u.id <> ?
         ORDER BY u.first_name"
    );
    $stmt->execute([$groupId, $userId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare(
    "SELECT t.id, t.title, t.created_at, u.first_name, u.last_name
     FROM topics t
     JOIN users u ON u.id = t.created_by
     WHERE t.group_id = ?
     ORDER BY t.created_at DESC"
);
$stmt->execute([$groupId]);
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $membership['name'];
require 'header.php';
?>
    <h1><?= e($membership['name']) ?></h1>
    <p><?= e($membership['description'] ?? '') ?></p>
    <p>Din roll: <strong><?= e($membership['role']) ?></strong></p>

    <?php if ($notice): ?>
        <p style="color:green;"><?= e($notice) ?></p>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
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
                        <input type="hidden" name="form_type" value="application">
                        <input type="hidden" name="applicant_id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" name="action" value="approve">Godkänn</button>
                        <button type="submit" name="action" value="reject">Neka</button>
                    </form>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Medlemmar</h2>
        <?php if (!$members): ?>
            <p>Du är ensam medlem så länge.</p>
        <?php else: ?>
            <ul>
            <?php foreach ($members as $m): ?>
                <li>
                    <?= e($m['first_name'] . ' ' . $m['last_name']) ?>
                    — <strong><?= e($m['role']) ?></strong>

                    <form method="post" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_type" value="role_change">
                        <input type="hidden" name="target_id" value="<?= (int)$m['id'] ?>">
                        <?php if ($m['role'] === 'admin'): ?>
                            <button type="submit" name="new_role" value="member">Gör till medlem</button>
                        <?php else: ?>
                            <button type="submit" name="new_role" value="admin">Gör till admin</button>
                        <?php endif; ?>
                    </form>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Diskussioner</h2>

    <?php if (!$topics): ?>
        <p>Inga diskussioner än. Starta den första!</p>
    <?php else: ?>
        <ul>
        <?php foreach ($topics as $t): ?>
            <li>
                <a href="topic.php?id=<?= (int)$t['id'] ?>"><?= e($t['title']) ?></a>
                <small>— startad av <?= e($t['first_name'] . ' ' . $t['last_name']) ?></small>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3>Starta ny diskussion</h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="form_type" value="new_topic">
        <p><input type="text" name="title" placeholder="Ämne"></p>
        <p><textarea name="body" placeholder="Ditt första inlägg"></textarea></p>
        <p><button type="submit">Skapa diskussion</button></p>
    </form>
<?php require 'footer.php'; ?>