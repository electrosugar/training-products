<?php

require_once 'common.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Page 404') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<h1><?= 404 ?></h1>
<h3><?= translateText('Page not found') ?></h3>
<a href="index.php"><?= translateText('Go back to index') ?></a>
</body>
</html>