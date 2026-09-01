<?php
require_once 'db.php';
require_once 'helpers.php';
require_login();

$pageTitle = 'Hem';
require 'header.php';
?>
    <h1>Välkommen, <?= e($_SESSION['user_name']) ?>!</h1>
    <p class="lead">
        Det här är forumet där du kan gå med i grupper kring dina intressen,
        starta diskussioner och prata med andra medlemmar. Skapa en egen grupp
        eller hitta en som passar dig.
    </p>

    <div class="home-grid">
        <a class="home-card" href="groups.php">
            <h3>Utforska grupper</h3>
            <p>Se grupper du är med i, och hitta nya att ansöka till.</p>
            <span class="arrow">Till grupper &rarr;</span>
        </a>
        
        <a class="home-card" href="create_group.php">
            <h3>Skapa en grupp</h3>
            <p>Starta en egen grupp och bjud in andra att diskutera.</p>
            <span class="arrow">Skapa grupp &rarr;</span>
        </a>
    </div>
<?php require 'footer.php'; ?>