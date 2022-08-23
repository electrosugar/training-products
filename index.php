<?php

require_once 'common.php';

if ($queryMarks = fetchQueryMarks()) {
    $selectProducts = 'SELECT * from products where not id in (' . $queryMarks . ')';
} else {
    $selectProducts = 'SELECT * from products';
}

$products = getProductsArray($queryMarks, $pdoConnection, $selectProducts);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!in_array(strip_tags($_POST['add']), $_SESSION['cart'])) {
        $selectProducts = 'SELECT * from products where id = ?';
        $statementSelectProducts = $pdoConnection->prepare($selectProducts);
        $statementSelectProducts->execute([$_POST['add']]);
        if ($fetchedProducts = $statementSelectProducts->fetchAll()) {
            array_push($_SESSION['cart'], strip_tags($_POST['add']));
            header('Location: index.php');
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
            <img src="images/<?= strip_tags($product['id']); ?>.png" alt="'.<?= strip_tags($product['id']); ?>.'-image"
                 class="roundImage">
            <div class="info">
                <span class="title"><?= strip_tags($product['title']); ?></span>
                <br>
                <span class="description"><?= strip_tags($product['description']); ?></span>
                <br>
                <span class="price"><?= strip_tags($product['price'] . getCurrency()); ?></span>
                <br>
            </div>
            <form action="index.php" method="post">
                <button type="submit" value="<?= strip_tags($product['id']); ?>"
                        name="add"><?= translateText('Add') ?></button>
            </form>
        </div>
    <?php endforeach ?>
</div>
<a href="cart.php"><?= translateText(' Go to cart '); ?></a>
<a href="login.php"><?= translateText(' Log in ') ?></a>
</body>
</html>