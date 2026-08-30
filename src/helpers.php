<?php

function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if(empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="'. csrf_token(). '">';
}

function check_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        die('Ogiltig förfrågan (CSRF).');
    }
}

function require_login(): void {
    if(!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function nav(): string {

    if (!isset($_SESSION['user_id'])) {
        return '';
    }

    $name = e($_SESSION['user_name'] ?? '');
    return <<<HTML
<nav style="margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid #ccc;">
    <a href="index.php">Hem</a> |
    <a href="groups.php">Grupper</a> |
    <a href="create_group.php">Skapa grupp</a>
    <span style="float:right;">
        Inloggad som {$name} &middot;
        <a href="logout.php">Logga ut</a>
    </span>
</nav>
HTML;
}