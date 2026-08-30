<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Gruppen måste ha ett namn.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO `groups` (name, description, created_by) VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $description, $_SESSION['user_id']]);
            $groupId = $pdo->lastInsertId();

            $stmt = $pdo->prepare(
                "INSERT INTO memberships (user_id, group_id, role, status)
                 VALUES (?, ?, 'admin', 'approved')"
            );
            $stmt->execute([$_SESSION['user_id'], $groupId]);

            $pdo->commit();
            header('Location: group.php?id=' . $groupId);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Kunde inte skapa gruppen, försök igen.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="sv">
<head><meta charset="utf-8"><title>Skapa grupp</title></head>
<body>
    <?= nav() ?>
    <h1>Skapa en ny grupp</h1>

    <?php if ($error): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>
    
    <form method="post">
        <?= csrf_field() ?>
        <p><input type="text" name="name" placeholder="Gruppens namn"></p>
        <p><textarea name="description" placeholder="Vad diskuteras i gruppen?"></textarea></p>
        <p><button type="submit">Skapa grupp</button></p>
    </form>
</body>
</html>
