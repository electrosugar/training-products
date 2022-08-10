<?php

require_once 'common.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['editProduct'])) {
    if(!empty($_POST['title'])){
        $title = $_POST['title'];
    }
    if(!empty($_POST['description'])){

    }
    if(!empty($_POST['price'])){

    }
    if(!empty($_POST['image'])){

    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Products</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<h1>Welcome <?= $_SESSION['username'] ?> !</h1>
<form enctype="multipart/form-data" action="products.php" method="post" class="form">
    <input type="text" name="title" placeholder="<?= translateText('Product Title') ?>"><br>
    <input type="text" name="description" placeholder="<?= translateText('Description') ?>" id="big"><br>
    <input type="text" name="price" placeholder="<?= translateText('Price') ?> "><br>
    <input type="file" name="image" value="Browse" placeholder="<?= translateText('Image') ?> "><br>
    <span class="formLinks"> <input type="submit" value="Save"></span>
</form>
<a href="products.php">Back to products</a>
<?php
die();
?>
</body>
</html>

