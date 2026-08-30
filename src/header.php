<?php
$pageTitle = $pageTitle ?? 'Forum';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
</head>
<body>
    <?= nav() ?>