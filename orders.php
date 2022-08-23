<?php

require_once 'common.php';
$databaseConnection = getDatabaseConnection();
$selectAllCustomers = $databaseConnection->prepare('select * from customers');
$selectAllCustomers->execute();

$customers = [];
$productArray = [];
foreach ($selectAllCustomers->fetchAll() as $row) {
    prepareOrderWithProducts($row, $customers);
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
    <?php foreach ($customers as $customerDetail): ?>
        <div class="order">
            <div class="product">
                <div class="info">
                    <span class="title"><?= translateText('Name: ') . strip_tags($customerDetail['name']); ?></span>
                    <br>
                    <span class="description"><?= translateText('Contact: ') . strip_tags($customerDetail['contact']); ?></span>
                    <br>
                    <span class="price"><?= translateText('Comment: ') . strip_tags($customerDetail['comment']); ?></span>
                    <br>
                    <span class="date"><?= translateText('Date: ') . strip_tags($customerDetail['creation_date']); ?></span>
                    <br>
                </div>
                <span><?= translateText('Total Price: ') . $customerDetail['price'] . getCurrency() ?></span>
            </div>
            <div class="selectedProducts">
                <?php foreach ($customerDetail['productArray'] as $product): ?>
                    <div class="product">
                        <img src="images/<?= strip_tags($product['id']); ?>OLD.png"
                             alt="'.<?= strip_tags($product['id']); ?>.'-image" class="roundImage">
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
    <?php endforeach ?>

</div>
<a href="cart.php">Go to cart</a>
<a href="cart.php">Go to index</a>
</body>
<?php

die();
?>
</html>