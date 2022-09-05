<?php

require_once 'common.php';

if ($queryMarks = fetchQueryMarks()) {
    $selectProducts = 'SELECT * FROM products WHERE id in (' . $queryMarks . ') AND deleted = 0';
    $products = getProductsArray($pdoConnection, $selectProducts, $queryMarks);
} else {
    $products = [];
}
//checks for items deleted that are still in the cart
if (isset($_SESSION['cart'])) {
    $selectProducts = 'SELECT * FROM products WHERE id = ? AND deleted = 0';
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
        $success = [];

        if (strlen($_POST['name']) > 50 || strlen($_POST['name']) === 0) {
            $failure['name'] = translateText('Name has to be under 50 characters and not empty');
        }
        if (strlen($_POST['contact']) > 50 || !filter_var($_POST['contact'], FILTER_VALIDATE_EMAIL) || strlen($_POST['contact']) === 0) {
            $failure['contact'] = translateText('Contact has to be under 50 characters and not empty and a valid email');
        }
        if (strlen($_POST['comment']) > 255 || strlen($_POST['comment']) === 0) {
            $failure['comment'] = translateText('Comment has to be under 255 characters and not empty');
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
            $mailTo = 'localhost';
            $mailSubject = 'Order # ' . $orderInfo['id'] . ' from ' . $orderInfo['name'];

            $boundaryText = '----*%$!$%*';
            $bound = '--' . $boundaryText . PHP_EOL;
            $boundaryFinal = '--' . $boundaryText . '--' . PHP_EOL;

            $mailHeaders = 'From: ' . 'localhost' . PHP_EOL;
            $mailHeaders .= 'Reply-To: ' . 'localhost' . PHP_EOL;
            $mailHeaders .= 'MIME-Version: 1.0' . PHP_EOL;
            $mailHeaders .= 'Content-Type: multipart/mixed; boundary=' . $boundaryText . PHP_EOL;

            $mailMessage = translateText(' You may wish to enable your email program to accept HTML ') . PHP_EOL . $bound;

            $mailMessage .= 'Content-Type: text/html; charset=UTF-8' . PHP_EOL .
                'Content-Transfer-Encoding: 7bit' . PHP_EOL . PHP_EOL;
            //variable used by the layout to show the view with inputs or without
            $display = false;
            ob_start();
            include 'layouts/cart.layout.php';
            $mailMessage .= ob_get_clean();

            $mailMessage .= PHP_EOL . PHP_EOL . $bound;
            foreach ($order['productArray'] as $product) {
                $productImage = file_get_contents('images/' . $product['id'] . '.png');
                $productImageData = base64_encode($productImage);
                $mailMessage .= 'Content-Type: image/png; name="' . $product['id'] . '.png"' . PHP_EOL
                    . 'Content-Transfer-Encoding: base64' . PHP_EOL
                    . 'Content-ID: <' . $product['id'] . '.png>' . PHP_EOL
                    . 'Content-Disposition: attachment' . PHP_EOL
                    . PHP_EOL
                    . chunk_split(base64_encode($productImage))
                    . $bound;
            }

            $mailMessage .= $boundaryFinal;
            $success['checkout'] = mail($mailTo, $mailSubject, $mailMessage, $mailHeaders);
            header('Location: order.php?orderId=' . $idOrder);
            die();

        } else {
            $failure['order'] = translateText('The order was not processed');
        }

    }
    $insertOrder = null;
    $insertOrderProductPivot = null;
    $selectProduct = null;
}

$title = 'Test';
?>
<!DOCTYPE html>
<html lang="en">
<?php
$display = true;

require_once 'layouts/cart.layout.php'; ?>
</html>