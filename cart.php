<?php

require_once 'common.php';

$pdoConnection = getDatabaseConnection();
if ($queryMarks = fetchQueryMarks()) {
    $selectProducts = 'SELECT * FROM products WHERE id in (' . $queryMarks . ')';
    $products = getProductsArray($pdoConnection, $selectProducts, $queryMarks);
} else {
    $products = [];
}
//checks for items deleted that are still in the cart
if (isset($_SESSION['cart'])) {
    $selectProducts = 'SELECT * FROM products WHERE id = ?';
    $statementSelectProducts = $pdoConnection->prepare($selectProducts);
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $statementSelectProducts->execute([$productId]);
        $fetchedProducts = $statementSelectProducts->fetchAll();
        if (!$fetchedProducts) {
            unset($_SESSION['cart'][$productId]);
        }
    }
    $statementSelectProducts = null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['productIdQuantity'])) {
        //4294967295 = 2^32-1 unsigned int limit for table
        if (empty($_POST['quantity']) ||
            !filter_var($_POST['quantity'], FILTER_VALIDATE_INT) ||
            !isset($_POST['quantity']) || $_POST['quantity'] <= 0 || $_POST['quantity'] >= 4294967295) {
            // default value
            $value = 1;
            $failure['quantity'][$_POST['productIdQuantity']] = translateText('Invalid quantity entered!');
        } else {
            $value = strip_tags($_POST['quantity']);
        }
        $idProduct = strip_tags($_POST['productIdQuantity']);
        $_SESSION['cart'][$idProduct] = $value;

    }

    if (isset($_POST['remove'])) {
        $idProduct = strip_tags($_POST['remove']);
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        } elseif (isset($_SESSION['cart'][$idProduct])) {
            unset($_SESSION['cart'][$idProduct]);
            header('Location: cart.php');
        }
    }

    if (isset($_POST['name']) && isset($_POST['contact']) && isset($_POST['comment'])) {
        $name = strip_tags($_POST['name']);
        $contact = strip_tags($_POST['contact']);
        $comment = strip_tags($_POST['comment']);
        $failure = [];

        if (strlen($_POST['name']) > 50 || strlen($_POST['name']) === 0) {
            $failure['name'] = 'Name has to be under 50 characters and not empty<br>';
        }
        if (strlen($_POST['contact']) > 50 || !filter_var($_POST['contact'], FILTER_VALIDATE_EMAIL) || strlen($_POST['contact']) === 0) {
            $failure['contact'] = 'Contact has to be under 50 characters and not empty and a valid email <br>';
        }
        if (strlen($_POST['comment']) > 255 || strlen($_POST['comment']) === 0) {
            $failure['comment'] = 'Comment has to be under 255 characters and not empty <br>';
        }

        if (!empty($name) && !empty($contact) && !empty($comment) && !empty($_SESSION['cart']) && !$failure) {
            $insertOrder = $pdoConnection->prepare('INSERT INTO orders (creation_date, name, contact, comment) VALUES (now(), ?, ?, ?)');
            $insertOrder->execute([$name, $contact, $comment]);
            $idOrder = $pdoConnection->lastInsertId();
            $insertOrderProductPivot = $pdoConnection->prepare('INSERT INTO order_product (id_order, id_product, price, quantity) VALUES ( ?, ?, ?, ?)');

            $selectProduct = $pdoConnection->prepare('SELECT * FROM products WHERE id = ?');

            foreach ($_SESSION['cart'] as $idProduct => $quantity) {
                $selectProduct->execute([$idProduct]);
                $selectedProduct = $selectProduct->fetch();
                $insertOrderProductPivot->execute([$idOrder, $idProduct, $selectedProduct['price'], $quantity]);
            }

            //fetch order
            $selectOrder = fetchOrderStatement();
            $selectOrder->execute([$idOrder]);
            $orderInfo = $selectOrder->fetch();

            $order = orderToArray($orderInfo);

            //create email with embedded images
            $mailTo = SHOP_MANAGER_EMAIL;
            $mailSubject = 'Order # ' . $orderInfo['id'] . ' from ' . $orderInfo['name'];

            $boundaryText = '----*%$!$%*';
            $bound = '--' . $boundaryText . PHP_EOL;
            $boundaryFinal = '--' . $boundaryText . '--' . PHP_EOL;

            $mailHeaders = 'From: ' . EMAIL_USERNAME . PHP_EOL;
            $mailHeaders .= 'Reply-To: ' . EMAIL_USERNAME . PHP_EOL;
            $mailHeaders .= 'MIME-Version: 1.0' . PHP_EOL;
            $mailHeaders .= 'Content-Type: multipart/mixed; boundary=' . $boundaryText . PHP_EOL;

            $mailMessage = ' You may wish to enable your email program to accept HTML ' . PHP_EOL . $bound;

            $mailMessage .= 'Content-Type: text/html; charset=UTF-8' . PHP_EOL .
                'Content-Transfer-Encoding: 7bit' . PHP_EOL . PHP_EOL;
            $mailMessage .= '<html>';
            $mailMessage .= '<head><style>' . file_get_contents('stylesheets/index.css') . '</style> </head>';
            $mailMessage .= '<body>';

            $mailMessage .= '<div class="order">';
            $mailMessage .= '<div class="product">';
            $mailMessage .= '<div class="info">';
            $mailMessage .= '<span class="title">' . translateText('Name: ') . $order['name'] . '</span>';
            $mailMessage .= '<br>';
            $mailMessage .= '<span class="description">' . translateText('Contact: ') . $order['contact'] . '</span>';
            $mailMessage .= '<br>';
            $mailMessage .= '<span class="price">' . translateText('Comment: ') . $order['comment'] . '</span>';
            $mailMessage .= '<br>';
            $mailMessage .= '<span class="date">' . translateText('Date: ') . $order['creation_date'] . '</span>';
            $mailMessage .= '<br>';
            $mailMessage .= '</div>';
            $mailMessage .= '<span>' . translateText('Total Price: ') . $order['totalPrice'] . getCurrency() . '</span>';
            $mailMessage .= '</div>';
            $mailMessage .= '<div class="selectedProducts">';
            foreach ($order['productArray'] as $product) {
                $mailMessage .= '<div class="product">';
                $mailMessage .= '<img src="cid:' . $product['id'] . '.png" class="roundImage">';
                $mailMessage .= '<div class="info">';
                $mailMessage .= '<span class="title">' . translateText('Title: ') . $product['title'] . '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '<span class="description">' . translateText('Description: ') . $product['description'] . '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '<span class="totalProductPrice">' . translateText('Price per item: ') . $product['price'] . getCurrency() . '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '<span class="quantity">' . translateText('Quantity: ') . $product['quantity'] . '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '<span class="price">' . translateText('Price: ') . $product['price'] * $product['quantity'] . getCurrency() . '</span>';
                $mailMessage .= '<br>';
                $mailMessage .= '</div>';
                $mailMessage .= '</div>';
                $mailMessage .= '<br>';
            }
            $mailMessage .= '</div>';
            $mailMessage .= '</div>';
            $mailMessage .= '<br>';

            $mailMessage .= '</body></html>' . PHP_EOL . PHP_EOL . $bound;
            foreach ($order['productArray'] as $product) {
                $productImage = file_get_contents('images/' . $product['id'] . '.png');
                $productImageData = base64_encode($productImage);
                $mailMessage .= 'Content-Type: image/png; name="' . $product['id'] . '.png"' . PHP_EOL
                    . 'Content-Transfer-Encoding: base64' . PHP_EOL
                    . 'Content-ID: <' . $product['id'] . '.png>' . PHP_EOL
                    . PHP_EOL
                    . chunk_split(base64_encode($productImage))
                    . $bound;
            }
            $success['checkout'] = 'Successful Checkout!';
            $mailMessage .= $boundaryFinal;
            mail($mailTo, $mailSubject, $mailMessage, $mailHeaders);
            header('Location: order.php?orderId=' . $idOrder);
            die();

        } else {
            $failure['order'] = 'The order was not processed';
        }

    }
    $insertOrder = null;
    $insertOrderProductPivot = null;
    $selectProduct = null;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Cart') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="products">
    <?php foreach ($products as $product): ?>
        <div class="product">
            <img src="images/<?= $product['id']; ?>.png" alt="<?= $product['id'] ?>-image"
                 height="100px" width="100px">
            <div class="info">
                <span class="title"><?= $product['title'] ?></span>
                <br>
                <span class="description"><?= $product['description'] ?></span>
                <br>
                <span class="price"><?= $product['price'] . getCurrency() . '*' . $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : '1' ?></span>
                <span><?= '= ' . $product['price'] * $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : 1 ?></span>
                <br>
                <form action="cart.php" method="post">
                    <label>
                        <?= translateText('Quantity:') . $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] . '<br>' : ('1' . '<br>') ?>
                        <input type="number" name="quantity" min="1">
                    </label>

                    <div class="error"><?= $value = isset($failure['quantity'][$product['id']]) ? $failure['quantity'][$product['id']] : '' ?></div>

                    <input type="hidden" name="productIdQuantity" value="<?= $product['id'] ?>" min="1">
                    <button type="submit"><?= translateText('Update') ?></button>
                </form>
            </div>

            <form action="cart.php" method="post">
                <input type="hidden" name="remove" value="<?= $product['id'] ?>">
                <button type="submit"><?= translateText('Remove') ?></button>
            </form>
        </div>
    <?php endforeach ?>
</div>
<form action="cart.php" method="post" class="form">
    <div class="error"> <?= isset($failure['order']) ? translateText($failure['order']) : '' ?></div>
    <div class="success"> <?= isset($success['checkout']) ? translateText($success['checkout']) : '' ?></div>

    <br>
    <?= translateText('Name') ?> <input type="text" name="name" placeholder="<?= translateText('Name') ?>"
                                        value="<?= $value = isset($_POST['name']) ? $_POST['name'] : '' ?>">
    <div class="error"><?= $value = isset($failure['name']) ? $failure['name'] : '' ?></div>
    <br>

    <?= translateText('Contact Details') ?> <input type="text" name="contact"
                                                   placeholder="<?= translateText('Contact Details') ?>"
                                                   value="<?= $value = isset($_POST['contact']) ? $_POST['contact'] : '' ?>">
    <div class="error"><?= $value = isset($failure['contact']) ? $failure['contact'] : '' ?></div>
    <br>

    <?= translateText('Comment') ?> <input type="text" name="comment" placeholder="<?= translateText('Comment') ?>"
                                           value="<?= $value = isset($_POST['comment']) ? $_POST['comment'] : '' ?>"
                                           id="big">
    <div class="error"><?= $value = isset($failure['comment']) ? $failure['comment'] : '' ?></div>
    <br>
    <span class="formLinks">
        <input type="submit" value="Checkout">
        <a href="index.php"><?= translateText('Go to index') ?></a>
        <a href="orders.php"><?= translateText('Go to orders') ?></a>
    </span>
</form>
<?php

die();
?>
</body>
</html>