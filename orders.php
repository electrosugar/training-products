<?php

require_once 'common.php';

$pdoConnection = getDatabaseConnection();
//$selectAllOrders = $pdoConnection->prepare('SELECT * FROM orders');
$selectAllOrders = $pdoConnection->prepare('SELECT   OP.id_order,
                                                               O.id,
                                                               O.name,
                                                               O.contact,
                                                               O.comment,
                                                               O.creation_date,
                                                               GROUP_CONCAT(COALESCE(P.title, \'NULL\') SEPARATOR \',\') AS titles,
                                                               GROUP_CONCAT(COALESCE(P.description, \'NULL\') SEPARATOR \',\') AS descriptions,
                                                               GROUP_CONCAT(OP.price SEPARATOR \',\') AS prices,
                                                               GROUP_CONCAT(OP.quantity SEPARATOR \',\') AS quantities,
                                                               SUM(OP.quantity * OP.price) AS totalPrice,
                                                               GROUP_CONCAT(COALESCE(OP.id_product, \'NULL\') SEPARATOR \',\') AS product_ids FROM products P
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
            <h2><?= translateText('Order #') . $order['id'] ?> </h2>
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
                <span><?= translateText('Total Price: ') . $order['totalPrice'] . getCurrency() ?></span>
            </div>
            <div class="selectedProducts">
                <?php foreach ($order['productArray'] as $product): ?>
                    <div class="product">
                        <img src="images/<?= $value = isset($product['id']) ? $product['id'] : translateText('missing id ') ?>.png"
                             alt="<?= $value = isset($product['id']) ? $product['id'] : translateText('missing id ') ?>-image"
                             class="roundImage">
                        <div class="info">
                            <span class="title"><?= translateText('Title: ') . $value = isset($product['title']) ? $product['title'] : '<span class="error">' . translateText('missing title ') . '</span>' ?></span>
                            <br>
                            <span class="description"><?= translateText('Description: ') . $value = isset($product['description']) ? $product['description'] : '<span class="error">' . translateText('missing description ') . '</span>' ?></span>
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
    <?php endforeach ?>

</div>
<a href="cart.php"><?= translateText('Go to cart') ?></a>
<a href="index.php"><?= translateText('Go to index') ?></a>
</body>
<?php

die();
?>
</html>