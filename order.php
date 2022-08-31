<?php

require_once 'common.php';

$orders = [];
$order = [];
$pdoConnection = getDatabaseConnection();


if (isset($_GET['orderId'])) {
    $selectOrder = $pdoConnection->prepare('SELECT * FROM orders WHERE id = ?');
    $selectOrder->execute([strip_tags($_GET['orderId'])]);

    $row = $selectOrder->fetch();
    prepareOrderWithProducts($row, $orders);
    $order = $orders[0];
    if(!$row){
        http_response_code(404);
    }
}else{
    http_response_code(404);
    include('404.php');
    die();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Order') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="orders">
    <div class="order">
        <h1><?= translateText('The order #') . $order['id'] . translateText(' has been recorded'); ?> </h1>
        <div class="product">
            <div class="info">
                <span class="title"><?= translateText('Name: ') . $order['name'] ?></span>
                <br>
                <span class="description"><?= translateText('Contact: ') . $order['contact'] ?></span>
                <br>
                <span class="price"><?= translateText('Comment: ') . $order['comment'] ?></span>
                <br>
                <span class="date"><?= translateText('Date: ') . $order['creation_date'] ?></span>
                <br>
            </div>
            <span><?= translateText('Total Price: ') . $order['price'] . getCurrency() ?></span>
        </div>
        <div class="selectedProducts">
            <?php foreach ($order['productArray'] as $product): ?>
                <div class="product">
                    <img src="images/<?= $product['id_product'] ?>.png"
                         alt="<?= $product['id_product'] ?>-image" class="roundImage">
                    <div class="info">
                        <span class="title"><?=translateText('Title: ') . $product['title'] ?></span>
                        <br>
                        <span class="description"><?=translateText('Description: ') . $product['description'] ?></span>
                        <br>
                        <span class="price"><?=translateText('Price per item: ') . $product['price'] . getCurrency() ?></span>
                        <br>
                        <span class="quantity"><?= translateText('Quantity: ') . $product['quantity'] ?></span>
                        <br>
                        <span class="quantity"><?= translateText('Price: ') . $product['price'] * $product['quantity']  . getCurrency()?></span>
                        <br>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
<a href="cart.php"><?= translateText('Go to cart') ?></a>
<a href="index.php"><?= translateText('Go to index') ?></a>
</body>
<?php

die();
?>
</html>