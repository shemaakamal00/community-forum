<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();
?>
<!DOCTYPE html>
<html lang="sv">
<head><meta charset="utf-8"><title>Forum</title></head>
<body>
    <h1>Välkommen, <?= e($_SESSION['user_name']) ?>!</h1>
    <?= nav() ?>
    <p> Gå till <a href="groups.php">Grupper</a> för att se dina grupper, skapa grupper eller gå med i en ny grupp.</p>
</body>
</html>