<?php

require_once 'common.php';

$customers = [];
$customer = [];
if (isset($_GET['showOrder'])) {
    $databaseConnection = getDatabaseConnection();
    $selectCustomer = $databaseConnection->prepare('select * from customers where id = ?');
    $selectCustomer->execute([strip_tags($_GET['showOrder'])]);

    $row = $selectCustomer->fetch();
    prepareOrderWithProducts($row, $customers);
    $customer = $customers[0];
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
        <h1>The order #<?= strip_tags($customer['id']); ?> has been recorded</h1>
        <div class="product">
            <div class="info">
                <span class="title"><?= translateText('Name: ') . strip_tags($customer['name']); ?></span>
                <br>
                <span class="description"><?= translateText('Contact: ') . strip_tags($customer['contact']); ?></span>
                <br>
                <span class="price"><?= translateText('Comment: ') . strip_tags($customer['comment']); ?></span>
                <br>
                <span class="date"><?= translateText('Date: ') . strip_tags($customer['creation_date']); ?></span>
                <br>
            </div>
            <span><?= translateText('Total Price: ') . $customer['price'] . getCurrency() ?></span>
        </div>
        <div class="selectedProducts">
            <?php foreach ($customer['productArray'] as $product): ?>
                <div class="product">
                    <img src="images/<?= strip_tags($product['id_product']); ?>.png"
                         alt="'.<?= strip_tags($product['id_product']); ?>.'-image" class="roundImage">
                    <div class="info">
                        <span class="title"><?= strip_tags($product['title']); ?></span>
                        <br>
                        <span class="description"><?= strip_tags($product['description']); ?></span>
                        <br>
                        <span class="price"><?= strip_tags($product['price'] . getCurrency()); ?></span>
                        <br>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
<a href="cart.php">Go to cart</a>
<a href="cart.php">Go to index</a>
</body>
<?php

die();
?>
</html>