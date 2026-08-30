<?php
require 'db.php';
require 'helpers.php';
require_login();
?>
<!DOCTYPE html>
<html lang="sv">
<head><meta charset="utf-8"><title>Forum</title></head>
<body>
    <h1>Välkommen, <?= e($_SESSION['user_name']) ?>!</h1>
    <p>Du är nu inloggad. Här kommer grupperna att dyka upp snart.</p>
    <p><a href="logout.php">Logga ut</a></p>
</body>
</html>