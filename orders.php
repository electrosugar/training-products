<?php

require_once 'common.php';
session_start();
$databaseConnection = getDatabaseConnection();
$selectAllCustomers = $databaseConnection->prepare('select * from customers');
$selectAllCustomers->execute();

$customers = [];
foreach($selectAllCustomers->fetchAll() as $row ) {
    $selectProductIds = $databaseConnection->prepare('select id_product from orders where id_customer = ?');
    if($selectProductIds){
        $selectProductIds->execute( [$row['id']]);
        $price = 0;
        while($productId = $selectProductIds->fetch()) {
            $selectPrice =  $databaseConnection->prepare('select title, price from products where id = ?');
            $selectPrice->execute([$productId['id_product']]);
            $price += $selectPrice->fetch()['price'];
        }
        $row['price'] = $price;
        $customers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Index</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="products">
    <?php foreach ($customers as $customerDetail): ?>
        <div class="product">
            <div class="info">
                <span class="title"><?= translateText('Name: ') . strip_tags($customerDetail['name']); ?></span>
                <br>
                <span class="description"><?= translateText('Contact: ') . strip_tags($customerDetail['contact']); ?></span>
                <br>
                <span class="price"><?= translateText('Comment: ') . strip_tags($customerDetail['comment']);?></span>
                <br>
                <span class="price"><?= translateText('Date: ') . strip_tags($customerDetail['creation_date']);?></span>
                <br>
            </div >
            <span><?= translateText('Total Price: ') . $customerDetail['price'].getCurrency() ?></span>
        </div>
        <br>
    <?php endforeach ?>
    <a href="cart.php"><?= translateText('Go to cart ') ?></a>
    <a href="order.php"><?= translateText('Advanced Orders ') ?></a>
</div>

</body>
</html>