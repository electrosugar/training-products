<?php

require_once 'common.php';
session_start();
$databaseConnection = getDatabaseConnection();
$selectAllCustomers = $databaseConnection->prepare('select * from customers');
$selectAllCustomers->execute();

$customers = [];
$productArray = [];
foreach($selectAllCustomers->fetchAll() as $row ) {
    $selectProductIds = $databaseConnection->prepare('select id_product from orders where id_customer = ?');
    if($selectProductIds){
        $selectProductIds->execute( [$row['id']]);
        $price = 0;
        $productArray = [];
        $productPriceIndex = 0;
        while($productId = $selectProductIds->fetch()) {
            $selectPrice =  $databaseConnection->prepare('select * from products where id = ?');
            $selectPrice->execute([$productId['id_product']]);
            $productArray[] = $selectPrice->fetch();
            $price += $productArray[$productPriceIndex]['price'];
            $productPriceIndex += 1;
        }
        $row['price'] = $price;
        $row['productArray'] = $productArray;
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
        <div class="order">
            <div class="product">
                <div class="info">
                    <span class="title"><?php echo translateText('Name: ') . htmlspecialchars($customerDetail['name']); ?></span>
                    <br>
                    <span class="description"><?php echo translateText('Contact: ') . htmlspecialchars($customerDetail['contact']); ?></span>
                    <br>
                    <span class="price"><?php echo translateText('Comment: ') . htmlspecialchars($customerDetail['comment']);?></span>
                    <br>
                    <span class="date"><?php echo translateText('Date: ') . htmlspecialchars($customerDetail['creation_date']);?></span>
                    <br>
                </div >
                <span><?php echo translateText('Total Price: ') . $customerDetail['price'].getCurrency() ?></span>
            </div>
            <div class="selectedProducts">
                <?php foreach ($customerDetail['productArray'] as $product): ?>
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
                    </div>
                    <br>
                <?php endforeach ?>
            </div>
        </div>
        <br>
    <?php endforeach ?>
    <a href="cart.php">Go to cart</a>
    <a href="cart.php">Go to index</a>
</div>

</body>
</html>