<?php

require_once 'common.php';
$products = getProducts('index');
session_start();
if(isset($_GET['action']) && $_GET['action'] == 'logout'){
    logout();
}
;?>
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
<?php foreach ($products as $product): ?>
    <div class="product">
        <img src="images/<?= strip_tags($product['id']); ?>.png" alt="'.<?= strip_tags($product['id']); ?>.'-image" height="100px" width="100px">
        <div class="info">
            <span class="title"><?= strip_tags($product['title']); ?></span>
            <br>
            <span class="description"><?= strip_tags($product['description']); ?></span>
            <br>
            <span class="price"><?= strip_tags($product['price'].getCurrency());?></span>
            <br>
        </div >
        <span><a href="product.php?editProduct=<?= strip_tags($product['id']); ?>">Edit   </a><a href="?deleteProduct=<?= strip_tags($product['id']); ?>">Delete</a></span>
    </div>
    <br>
<?php endforeach ?>
<a href="product.php">Add product</a>
<a href="?action=logout">Logout</a>
<?php

die();
?>
</body>
</html>
