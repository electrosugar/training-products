<?php

require_once 'common.php';

$customers = [];
$customer = [];
$pdoConnection = getDatabaseConnection();


if (isset($_GET['showOrder'])) {
    $selectCustomer = $pdoConnection->prepare('select * from customers where id = ?');
    $selectCustomer->execute([strip_tags($_GET['showOrder'])]);

    $row = $selectCustomer->fetch();

    prepareOrderWithProducts($row, $customers);
    $customer = $customers[0];
    if(!$row){
        resetCustomer($customers);
    }
}else{
    resetCustomer($customers);
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
        <h1><?= translateText('The order #') . $customer['id'] . translateText('has been recorded'); ?> </h1>
        <div class="product">
            <div class="info">
                <span class="title"><?= translateText('Name: ') . $customer['name'] ?></span>
                <br>
                <span class="description"><?= translateText('Contact: ') . $customer['contact'] ?></span>
                <br>
                <span class="price"><?= translateText('Comment: ') . $customer['comment'] ?></span>
                <br>
                <span class="date"><?= translateText('Date: ') . $customer['creation_date'] ?></span>
                <br>
            </div>
            <span><?= translateText('Total Price: ') . $customer['price'] . getCurrency() ?></span>
        </div>
        <div class="selectedProducts">
            <?php foreach ($customer['productArray'] as $product): ?>
                <div class="product">
                    <img src="images/<?= $product['id_product'] ?>.png"
                         alt="<?= $product['id_product'] ?>-image" class="roundImage">
                    <div class="info">
                        <span class="title"><?=translateText('Title: ') . $product['title'] ?></span>
                        <br>
                        <span class="description"><?=translateText('Description: ') . $product['description'] ?></span>
                        <br>
                        <span class="price"><?=translateText('Price: ') . $product['price'] . getCurrency() ?></span>
                        <br>
                        <span class="quantity"><?= translateText('Quantity: ') . $product['quantity'] ?></span>
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