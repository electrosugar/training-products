<?php

require_once 'common.php';
session_start();
    $databaseConnection = getDatabaseConnection();
    $selectAllCustomers = $databaseConnection->prepare('select * from customers');
    $selectAllCustomers->execute();
    $resultedCustomers =  $selectAllCustomers->get_result();

    $customers = array();
    if (isset($resultedCustomers) && $resultedCustomers->num_rows > 0) {
        while($row = $resultedCustomers->fetch_assoc()) {
            $selectProductIds = $databaseConnection->prepare('select id_product from orders where id_customer = ?');
            if($selectProductIds){
                $selectProductIds->bind_param('i', $row['id']);
                $selectProductIds->execute();
                $productIds = $selectProductIds->get_result();
                $price = 0;
                while($productId = $productIds->fetch_assoc()) {
                    $selectPrice =  $databaseConnection->prepare('select title, price from products where id = ?');
                    $selectPrice->bind_param('i', $productId['id_product']);
                    $selectPrice->execute();
                    $price += $selectPrice->get_result()->fetch_assoc()['price'];
                }
                $row['price'] = $price;
                $customers[] = $row;
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
    <title>Index</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="products">
    <?php foreach ($customers as $customerDetail): ?>
        <div class="product">
            <div class="info">
                <span class="title"><?php echo translateText('Name: ') . htmlspecialchars($customerDetail['name']); ?></span>
                <br>
                <span class="description"><?php echo translateText('Contact: ') . htmlspecialchars($customerDetail['contact']); ?></span>
                <br>
                <span class="price"><?php echo translateText('Comment: ') . htmlspecialchars($customerDetail['comment']);?></span>
                <br>
                <span class="price"><?php echo translateText('Date: ') . htmlspecialchars($customerDetail['creation_date']);?></span>
                <br>
            </div >
            <span><?php echo translateText('Total Price: ') . $customerDetail['price'].getCurrency() ?></span>
        </div>
        <br>
    <?php endforeach ?>
    <a href="cart.php">Go to cart</a>
    <a href="cart.php">Go to index</a>
</div>

</body>
</html>