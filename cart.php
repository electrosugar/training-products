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

            $insertOrder = $pdoConnection->prepare('INSERT INTO orders (id_customer, id_product) VALUES ( ?, ?)');
            foreach($_SESSION['cart'] as $idProduct){
                $insertOrder->execute([$idCustomer, $idProduct]);
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
            $mailMessage .= '<head><style>
                                    body{
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                    }
                                    .product{
                                        border:2px solid black;
                                        display: flex;
                                        flex-direction: row;
                                        align-items: center;
                                        padding: 1em;
                                        margin: 1em;
                                    }
                                    .products{
                                        display: flex;
                                        flex-direction: row;
                                        align-items: center;
                                        flex-wrap: wrap;
                                    }
                                    .info{
                                        text-align:left;
                                        padding: 1em;
                                        margin: 1em;
                                        border:2px dashed black;
                                    }
                                    .order{
                                        border: 5px solid dodgerblue;
                                        padding: 1em;
                                        margin: 4em;
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                    }
                                    
                                    .selectedProducts{
                                        border: 5px solid lightseagreen;
                                        padding: 1em;
                                        display: flex;
                                        flex-direction: row;
                                        align-items: center;
                                        flex-wrap: wrap;
                                    }</style> </head>';
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
                    $mailMessage .= '<img src="cid:'.$product['id'].'.png" width="100px" height="100px">';
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
                    $productImage = file_get_contents('images/'.$product['id'].'.png');
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
        <br>
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