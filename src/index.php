<?php 
$host = 'db';
$db = 'forum';
$user = 'forumuser';
$pass = 'forumpassword';
 
try {
    $pdo = new PDO("mysql:host=$host; dbname=$db; charset=utf8mb4", $user, $pass);
    echo "<h1> Det fungerar! </h1>";
    echo "<p> PHP kör och kan ansluta till databasen. </p>";
} catch (PDOException $e) {
    echo "Det fungerar inte:" .$e->getMessage();
}