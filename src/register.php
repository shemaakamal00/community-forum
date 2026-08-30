<?php

require_once 'db.php';
require_once 'helpers.php';

$error = '';

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = ($_POST['password'] ?? '');

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '' ) {
        $error = 'Alla fält måste fyllas i.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ogiltig e-postadress.';
    } elseif (strlen($password) < 8) {
        $error = 'Lösenordet måste vara minst 8 tecken.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (first_name, last_name, email, password_hash)
                VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$firstName, $lastName, $email, $hash]);

            header ('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            $error = ($e->errorInfo[1] == 1062)
            ? 'Den e-postadressen är redan registrerad.'
            : 'Något gick fel, försök igen.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="sv">
    <head><meta charset="UTF-8"><title>Skapa konto</title></head>
    <body>
        <h1>Skapa konto</h1>

        <?php if ($error): ?>
            <p style="color:red;"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <p><input type="text" name="first_name" placeholder="Förnamn"></p>
                <p><input type="text" name="last_name" placeholder="Efternamn"></p>
                <p><input type="email" name="email" placeholder="E-post"></p>
                <p><input type="password" name="password" placeholder="Lösenord (minst 8 tecken)"></p>
                <p><button type="submit">Registrera</button></p>
            </form>

            <p>Har du redan ett konto? <a href="login.php">Logga in</a></p>
    </body>
</html>