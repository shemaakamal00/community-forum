<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();

$userId = $_SESSION['user_id'];
$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare(
    "SELECT id, group_id FROM invites
    WHERE token = ? AND used_at IS NULL AND expires_at > NOW()"
);
$stmt->execute([$token]);
$invite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invite) {
    require 'header.php';
    echo '<h1>Ogiltig inbjudan</h1>';
    echo '<p>Länken är felaktig, redan använd eller har gått ut.</p>';
    require 'footer.php';
    exit;
}

$groupId = $invite['group_id'];

$stmt = $pdo->prepare(
    "SELECT status FROM memberships WHERE user_id = ? AND group_id = ?"
);
$stmt->execute([$userId, $groupId]);
$existing = $stmt->fetch(PDO:: FETCH_ASSOC);

try {
    $pdo->beginTransaction();

    if($existing) {
        $stmt = $pdo->prepare(
            "UPDATE memberships SET status = 'approved'
            WHERE user_id = ? AND group_id = ?"
        );
        $stmt->execute([$userId, $groupId]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO memberships (user_id, group_id, role, status)
            VALUES (?, ?, 'member', 'approved')"
        );
        $stmt->execute([$userId, $groupId]);
    }

    $stmt = $pdo->prepare(
        "UPDATE invites SET used_at = NOW(), used_by = ?
        WHERE id = ? AND used_at IS NULL"
    );
    $stmt->execute([$userId, $invite['id']]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    require 'header.php';
    echo'<p>Något gick fel, försök igen senare.</p>';
    require 'footer.php';
    exit;
}

header('Location: group.php?id=' . $groupId);
exit;