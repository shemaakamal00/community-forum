<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();

$userId = $_SESSION['user_id'];
$topicId = (int)($_GET['id'] ?? 0);
$notice = '';

$stmt = $pdo->prepare(
    "SELECT t.id, t.title, t.group_id, g.name AS group_name
    FROM topics t
    JOIN `groups` g ON g.id = t.group_id
    WHERE t.id = ?"
);
$stmt->execute([$topicId]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$topic) {
    http_response_code(404);
    echo nav();
    exit('<p>Diskussionen finns inte.</p>');
}

$stmt = $pdo->prepare(
    "SELECT 1 FROM memberships
    WHERE user_id = ? AND group_id = ? AND status = 'approved'"
);
$stmt->execute([$userId, $topic['group_id']]);
$isMember = (bool)$stmt->fetch();

if(!$isMember) {
    http_response_code(403);
    echo nav();
    exit('<p>Du har inte tillgång till den här diskussionen.</p>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $body = trim($_POST['body'] ?? '');

    if($body !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO posts (topic_id, user_id, body)
            VALUES (?, ?, ?)"
        );
        $stmt->execute([$topicId, $userId, $body]);

        header('Location: topic.php?id=' . $topicId);
        exit;
    } else {
        $notice = 'Svaret får inte vara tomt.';
    }
}

$stmt = $pdo->prepare(
    "SELECT p.body, p.created_at, u.first_name, u.last_name
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.topic_id = ?
    ORDER BY p.created_at ASC"
);
$stmt->execute([$topicId]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sv">
<head><meta charset="utf-8"><title><?= e($topic['title']) ?></title></head>
<body>
    <?= nav() ?>

    <p><a href="group.php?id=<?= (int)$topic['group_id'] ?>">&larr; Tillbaka till <?= e($topic['group_name']) ?></a></p>

    <h1><?= e($topic['title']) ?></h1>

    <?php if ($notice): ?><p style="color:red;"><?= e($notice) ?></p><?php endif; ?>

    <?php foreach ($posts as $p): ?>
        <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
            <p><?= nl2br(e($p['body'])) ?></p>
            <small>
                <?= e($p['first_name'] . ' ' . $p['last_name']) ?>
                &middot; <?= e($p['created_at']) ?>
            </small>
        </div>
    <?php endforeach; ?>

    <h3>Svara</h3>
    <form method="post">
        <?= csrf_field() ?>
        <p><textarea name="body" placeholder="Skriv ett svar"></textarea></p>
        <p><button type="submit">Skicka svar</button></p>
    </form>
</body>
</html>