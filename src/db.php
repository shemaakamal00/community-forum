<?php
session_start();

$host = 'db';
$db = 'forum';
$user = 'forumuser';
$pass = 'forumpassword';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log("Databas fel: " . $e->getMessage());
    die("Ett tekniskt fel har inträffat. Vänligen försök igen senare.");
}