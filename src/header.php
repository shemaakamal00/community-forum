<?php
$pageTitle = $pageTitle ?? 'MötesPlatsen';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?= nav() ?>
    <div class="container">