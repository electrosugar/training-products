<?php

require_once 'common.php';

$orders = [];
$order = [];

if (isset($_GET['orderId'])) {
    $selectOrder = fetchOrderStatement();
    $selectOrder->execute([strip_tags($_GET['orderId'])]);
    $row = $selectOrder->fetch();
    $order = orderToArray($row);
    if (!$row) {
        redirect404();
    }
} else {
    redirect404();
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
        <?php require_once 'layouts/order.layout.php'; ?>

        <div class="selectedProducts">
            <?php foreach ($order['productArray'] as $product): ?>
                <div class="product">
                    <img src="images/<?= $product['id'] ?>.png"
                         alt="<?= $product['id'] ?>-image" class="roundImage">
                    <div class="info">
                        <span class="title"><?= translateText('Title: ') . $product['title'] ?></span>
                        <br>
                        <span class="description"><?= translateText('Description: ') . $product['description'] ?></span>
                        <br>
                        <span class="price"><?= translateText('Price per item: ') . $product['price'] . getCurrency() ?></span>
                        <br>
                        <span class="quantity"><?= translateText('Quantity: ') . $product['quantity'] ?></span>
                        <br>
                        <span class="quantity"><?= translateText('Price: ') . $product['price'] * $product['quantity'] . getCurrency() ?></span>
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