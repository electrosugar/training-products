<?php

require_once 'common.php';
session_start();

$products = getProducts('cart');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach($_POST as $productId => $value){
        if($value == translateText('Remove')){
            if(!isset($_SESSION['cart'])){
                $_SESSION['cart'] = [];
            }
            if (($key = array_search($productId, $_SESSION['cart'])) !== false) {
                unset($_SESSION['cart'][$key]);
            }
            header("Refresh:0");
        }
    }

    if (isset($_POST['name']) && isset($_POST['contact']) && isset($_POST['comment'])) {
        $name = strip_tags($_POST['name']);
        $contact = strip_tags($_POST['contact']);
        $comment = strip_tags($_POST['comment']);

        $productsConnection = getDatabaseConnection();
        if(!empty($name) && !empty($contact) && !empty($comment)){
            $insertCustomers = $productsConnection->prepare('INSERT INTO customers (creation_date, name, contact, comment) VALUES (now(), ?, ?, ?)');
            $insertCustomers->bind_param('sss', $name, $contact, $comment);
            $insertCustomers->execute();
            $idCustomer = $insertCustomers->insert_id;

            $insertOrder = $productsConnection->prepare('INSERT INTO orders (id_customer, id_product) VALUES ( ?, ?)');
            foreach($_SESSION['cart'] as $idProduct){
                $insertOrder->bind_param('ii', $idCustomer, $idProduct);
                $insertOrder->execute();
            }
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
    <title>Cart</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="products">
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
            <form action="cart.php" method="post">
                <input type="submit" name="<?= strip_tags($product['id']); ?>" value="<?= translateText('Remove'); ?>">
            </form>
        </div>
        <br>
    <?php endforeach ?>
</div>
<form action="cart.php" method="post" class="form">
    <?= translateText("Name") ?>: <input type="text" name="name" placeholder="<?= translateText('Name'); ?>"><br>
    <?= translateText('Contact Details') ?> <input type="text" name="contact" placeholder="<?= translateText('Contact Details'); ?>"><br>
    <?= translateText('Comment') ?> <input type="text" name="comment" placeholder="<?= translateText('Comment'); ?>" id="big"><br>
    <span class="formLinks"> <input type="submit" value="Checkout"><a href="index.php"><?= translateText('Go to index'); ?></a><a href="orders.php">Go to orders</a></span>
</form>
<?php
    die();
?>
</body>
</html>