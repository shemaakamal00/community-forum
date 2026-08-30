<?php
require_once 'db.php';
require_once 'helpers.php';

$error = '';
$notice = isset($_GET['registered']) ? 'Konto skapat! Logga in nedan.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Fel e-post eller lösenord.';
    }
}
$pageTitle = 'Logga in'; 
require 'header.php'; 
?>
    <h1>Logga in</h1>

    <?php if ($notice): ?><p style="color:green;"><?= e($notice) ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>

        <form method="post">
        <?= csrf_field() ?>
        <p><input type="email" name="email" placeholder="E-post"></p>
        <p><input type="password" name="password" placeholder="Lösenord"></p>
        <p><button type="submit">Logga in</button></p>
    </form>

    <p>Inget konto? <a href="register.php">Skapa ett här</a></p>
<?php require 'footer.php'; ?>