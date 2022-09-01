<?php

require_once 'common.php';

if ($queryMarks = fetchQueryMarks()) {
    $selectProducts = 'SELECT * FROM products WHERE NOT id IN (' . $queryMarks . ')';
} else {
    $selectProducts = 'SELECT * FROM products';
}
$pdoConnection = getDatabaseConnection();
$products = getProductsArray($pdoConnection, $selectProducts, $queryMarks);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    //check for duplicates so it doesnt add the same product id twice
    if (!in_array($_POST['add'], $_SESSION['cart'])) {
        $selectProducts = 'SELECT * FROM products WHERE id = ?';
        $statementSelectProducts = $pdoConnection->prepare($selectProducts);
        $productId = strip_tags($_POST['add']);

        $statementSelectProducts->execute([$productId]);
        if ($fetchedProducts = $statementSelectProducts->fetchAll()) {
            $_SESSION['cart'] += [$productId => 1];
            header('Location: index.php');
            die();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Index') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="products">
    <?php foreach ($products as $product): ?>
        <div class="product">
            <img src="images/<?= $product['id']; ?>.png" alt="<?= $product['id'] ?>-image"
                 class="roundImage">
            <div class="info">
                <span class="title"><?= $product['title'] ?></span>
                <br>
                <span class="description"><?= $product['description'] ?></span>
                <br>
                <span class="price"><?= $product['price'] . getCurrency() ?></span>
                <br>
            </div>
            <form action="index.php" method="post">
                <input type="hidden" name="add" value="<?= $product['id'] ?>">
                <button type="submit"><?= translateText('Add') ?></button>
            </form>
        </div>
    <?php endforeach ?>
</div>
<a href="cart.php"><?= translateText('Go to cart') ?></a>
<a href="login.php"><?= translateText('Log in') ?></a>
</body>
</html>