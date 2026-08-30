<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();

$pageTitle = 'Hem';
require 'header.php';
?>
    <h1>Välkommen, <?= e($_SESSION['user_name']) ?>!</h1>
    <p>Gå till <a href="groups.php">Grupper</a> för att skapa eller gå med i en grupp.</p>
<?php require 'footer.php'; ?>