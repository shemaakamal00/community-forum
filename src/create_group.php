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


$pageTitle = 'Skapa grupp';
require 'header.php';
?>
    <h1>Skapa en ny grupp</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    
    <div class="card">
        <form method="post">
            <?= csrf_field() ?>
            <p><input type="text" name="name" placeholder="Gruppens namn"></p>
            <p><textarea name="description" placeholder="Vad diskuteras i gruppen?"></textarea></p>
            <p><button type="submit">Skapa grupp</button></p>
        </form>
    </div>
<?php require 'footer.php'; ?>