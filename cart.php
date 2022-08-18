<?php

require_once 'common.php';
session_start();

$products = getProducts('cart');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['remove'])){
        if(!isset($_SESSION['cart'])){
            $_SESSION['cart'] = [];
        }
        elseif (($key = array_search($_POST['remove'], $_SESSION['cart'])) !== false) {
            unset($_SESSION['cart'][$key]);
            header("Refresh:0");
        }
    }

    if (isset($_POST['name']) && isset($_POST['contact']) && isset($_POST['comment'])) {
        $name = strip_tags($_POST['name']);
        $contact = strip_tags($_POST['contact']);
        $comment = strip_tags($_POST['comment']);

        $pdoConnection = getDatabaseConnection();
        if(!empty($name) && !empty($contact) && !empty($comment) && !empty($_SESSION['cart'])){
            $insertCustomers = $pdoConnection->prepare('INSERT INTO customers (creation_date, name, contact, comment) VALUES (now(), ?, ?, ?)');
            $insertCustomers->execute([$name, $contact, $comment]);
            $idCustomer = $pdoConnection->lastInsertId();
            $insertOrder = $pdoConnection->prepare('INSERT INTO orders (id_customer, id_product, id_old_product) VALUES ( ?, ?, ?)');

            $selectProduct = $pdoConnection->prepare('SELECT * from products where id = ?');
            $selectOldProduct = $pdoConnection->prepare('SELECT * from old_products  where title = ? and description = ? and price = ?');
            $insertOldProduct = $pdoConnection->prepare('INSERT INTO old_products (title, description, price) VALUES ( ?, ?, ?)');

            foreach($_SESSION['cart'] as $idProduct){
                //insert the id_product from the old_products table but only if the product is changed and you need to create a new id in the old products table otherwise use what you already have
                //select from db old_products
                $selectProduct -> execute([$idProduct]);
                $selectedProduct = $selectProduct->fetch();
                $selectOldProduct -> execute([$selectedProduct['title'], $selectedProduct['description'], $selectedProduct['price']]);
                $oldProduct = $selectOldProduct->fetch();
                if($oldProduct['id'] === $idProduct){
                    $insertOrder->execute([$idCustomer, $idProduct, $idProduct]);
                }
                else{
                    $insertOldProduct->execute([$selectedProduct['title'], $selectedProduct['description'], $selectedProduct['price']]);
                    $target_dir = 'images/';
                    $source = $target_dir . $idProduct . '.png';
                    $destination = $target_dir .$pdoConnection->lastInsertId(). 'OLD.png';
                    copy($source, $destination);
                    $insertOrder->execute([$idCustomer, $idProduct, $pdoConnection->lastInsertId()]);
                }
                //any product added to products is added to old products as well
            }

            //send email
            $customer = [];
            $selectCustomers = $pdoConnection->prepare('SELECT * FROM customers WHERE id = ?');
            $selectCustomers->execute([$idCustomer]);
            $customerInfo = $selectCustomers->fetch();
            prepareOrderWithProducts($customerInfo, $customer);
            $mailTo = SHOP_MANAGER_EMAIL;
            $mailSubject = 'Order # '. $customerInfo['id'] . ' from ' . $customerInfo['name'];

            $boundaryText = '----*%$!$%*';
            $bound = '--'.$boundaryText.PHP_EOL;
            $boundaryFinal = '--'.$boundaryText.'--'.PHP_EOL;

            $mailHeaders = 'From: ' . EMAIL_USERNAME . PHP_EOL;
            $mailHeaders .= 'Reply-To: ' . EMAIL_USERNAME . PHP_EOL;
            $mailHeaders .= 'MIME-Version: 1.0' . PHP_EOL;
            $mailHeaders .= 'Content-Type: multipart/mixed; boundary='.$boundaryText.PHP_EOL ;

            $mailMessage = ' You may wish to enable your email program to accept HTML '.PHP_EOL. $bound;

            $mailMessage .= 'Content-Type: text/html; charset=UTF-8'.PHP_EOL.
                            'Content-Transfer-Encoding: 7bit'.PHP_EOL.PHP_EOL;
            $mailMessage .= '<html>';
            $mailMessage .= '<head><style>'.file_get_contents('stylesheets/index.css').'</style> </head>';
            $mailMessage .= '<body>';
            foreach ($customer as $customerDetail){
                $mailMessage .= '<div class="order">';
                $mailMessage .= '<div class="product">';
                $mailMessage .= '<div class="info">';
                $mailMessage .= '<span class="title">'. translateText('Name: ') . strip_tags($customerDetail['name']) .'</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '<span class="description">' . translateText('Contact: ') . strip_tags($customerDetail['contact']). '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '<span class="price">' . translateText('Comment: ') . strip_tags($customerDetail['comment']). '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '<span class="date">' . translateText('Date: ') . strip_tags($customerDetail['creation_date']). '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '</div>';
                $mailMessage .= '<span>' .translateText('Total Price: ') . $customerDetail['price'].getCurrency() . '</span>';
                $mailMessage .= '</div>';
                $mailMessage .= '<div class="selectedProducts">';
                foreach ($customerDetail['productArray'] as $product){
                    $mailMessage .= '<div class="product">';
                    $mailMessage .= '<img src="cid:'.$product['id'].'.png" class="roundImage">';
                    $mailMessage .= '<div class="info">';
                    $mailMessage .= '<span class="title">' .strip_tags($product['title']). '</span>';
                    $mailMessage .= '<br>';
                    $mailMessage .= '<span class="description">' .strip_tags($product['description']). '</span>';
                    $mailMessage .= '<br>';
                    $mailMessage .= '<span class="price">' .strip_tags($product['price']). getCurrency(). '</span>';
                    $mailMessage .= '<br>';
                    $mailMessage .= '</div>';
                    $mailMessage .= '</div>';
                    $mailMessage .= '<br>';
                }
                $mailMessage .= '</div>';
                $mailMessage .= '</div>';
                $mailMessage .= '<br>';

            }

            $mailMessage .= '</body></html>'.PHP_EOL.PHP_EOL.$bound;
            foreach ($customer as $customerDetail){
                foreach ($customerDetail['productArray'] as $product){
                    if(file_exists('images/'.$product['id'].'OLD.png')){
                        $productImage = file_get_contents('images/'.$product['id'].'OLD.png');
                    }
                    else{
                        $productImage = file_get_contents('images/'.$product['id_product'].'.png');
                    }
                    $productImageData = base64_encode($productImage);
                    $mailMessage .= 'Content-Type: image/png; name="'. $product['id'].'.png"'.PHP_EOL
                        .'Content-Transfer-Encoding: base64'.PHP_EOL
                        .'Content-ID: <'.$product['id'].'.png>'.PHP_EOL
                        .PHP_EOL
                        .chunk_split(base64_encode($productImage))
                        .$bound;
                }
            }

            $mailMessage .= $boundaryFinal;
            header('Location: order.php?showOrder='.$idCustomer);
            mail($mailTo, $mailSubject, $mailMessage, $mailHeaders) ;


        }
        else{
            echo 'The order was not processed';
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
    <title>Cart</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="products">
    <?php foreach ($products as $product): ?>
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
            <form action="cart.php" method="post">
                <button type="submit" value="<?= strip_tags($product['id']); ?>" name='remove'><?= translateText('Remove')?></button>
            </form>
        </div>
    <?php endforeach ?>
</div>
<form action="cart.php" method="post" class="form">
    <?= translateText('Name') ?> <input type="text" name="name" placeholder="<?= translateText('Name');?>" value="<?= $value = isset($_POST['name'])?$_POST['name']:''; ?>"><br>
    <?= translateText('Contact Details') ?> <input type="text" name="contact" placeholder="<?= translateText('Contact Details'); ?>" value="<?= $value = isset($_POST['contact'])?$_POST['contact']:''; ?>"><br>
    <?= translateText('Comment') ?> <input type="text" name="comment" placeholder="<?= translateText('Comment'); ?>" value="<?= $value = isset($_POST['comment'])?$_POST['comment']:''; ?>" id="big"><br>
    <span class="formLinks"> <input type="submit" value="Checkout"><a href="index.php"><?= translateText('Go to index'); ?></a><a href="orders.php">Go to orders</a></span>
</form>
<?php
    die();
?>
</body>
</html>