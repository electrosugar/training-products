<?php

require_once 'common.php';
checkLogin();

$selectAllOrders = $pdoConnection->prepare('SELECT   OP.id_order,
                                                               O.id,
                                                               O.name,
                                                               O.contact,
                                                               O.comment,
                                                               O.creation_date,
                                                               SUM(OP.quantity * OP.price) AS totalPrice FROM products P
                                                               RIGHT OUTER JOIN order_product OP ON OP.id_product = P.id  INNER JOIN orders O ON O.id = OP.id_order GROUP BY OP.id_order');
$selectAllOrders->execute();

$orders = [];
foreach ($selectAllOrders->fetchAll() as $row) {
    $orders[] = orderToArray($row);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Orders') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="products">
    <?php foreach ($orders as $order): ?>
        <div class="order">
            <?php require 'layouts/order.layout.php'; ?>
            <a href="order.php?orderId=<?= $order['id'] ?>"><?= translateText('View Order Details') ?></a>
        </div>
    <?php endforeach ?>
</body>
</div>
<a href="products.php"><?= translateText('Go to products') ?></a>
<a href="product.php"><?= translateText('Go to product') ?></a>
</body>
<?php

die();
?>
</html>