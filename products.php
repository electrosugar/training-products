<?php

require_once 'common.php';
$selectProducts = 'SELECT * FROM products';
$products = getProductsArray($pdoConnection, $selectProducts);

checkLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $selectProduct = $pdoConnection->prepare('SELECT OP.id_order
                                                                   FROM products P
                                                                   INNER JOIN order_product OP ON OP.id_product = P.id 
                                                                   INNER JOIN orders O ON O.id = OP.id_order WHERE P.id = ? GROUP BY OP.id_product');
        $selectProduct->execute([strip_tags($_POST['delete'])]);

        if(empty($selectProduct->fetch())){
            $deleteProduct = $pdoConnection->prepare('DELETE FROM products WHERE id=? LIMIT 1');
            if ($deleteProduct->execute([strip_tags($_POST['delete'])]) === TRUE) {
                $success['delete'] = 'Record deleted successfully!';
            } else {
                $failure['delete'][$_POST['delete']] = 'Failed deleting record from table!';
            }
            header('Location: products.php');
            die();
        }
        else {
            $failure['delete'][$_POST['delete']] = 'Failed deleting record, there are orders containing it!';
        }

    }

    if (isset($_POST['logout'])) {
        logout();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Products') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<a href="product.php?addProduct=true"><?= translateText('Add Product') ?> </a>
<a href="orders.php"><?= translateText('Go to orders') ?></a>
<form action="products.php" method="post">
    <input type="hidden" name="logout" value="true">
    <button type="submit"><?= translateText('Logout') ?></button>
</form>
<h1> <?= translateText('Welcome back, ') . $_SESSION['username'] ?> !</h1>
<div class="success"><?= isset($success['delete']) ? translateText($success['delete']) : '' ?></div>
<?php foreach ($products as $product): ?>
    <div class="product">
        <img src="images/<?= $product['id'] ?>.png" alt="<?= $product['id'] ?>-image"
             height="100px" width="100px">
        <div class="info">
            <span class="title"><?= $product['title'] ?></span>
            <br>
            <span class="description"><?= $product['description'] ?></span>
            <br>
            <span class="price"><?= $product['price'] . getCurrency() ?></span>
            <br>
        </div>
        <span>
            <a href="product.php?productId=<?= $product['id'] ?>"><?= translateText('Edit Items') ?></a>
            <form action="products.php" method="post">
                <input type="hidden" name="delete" value="<?= $product['id'] ?>">
                <button type="submit"><?= translateText('Delete') ?></button>
            </form>
        </span>
    </div>
    <div class="error"><?= isset($failure['delete'][$product['id']]) ? translateText($failure['delete'][$product['id']]) : '' ?></div>

    <br>
<?php endforeach ?>
<a href="product.php?addProduct=true"><?= translateText('Add Product') ?> </a>
<a href="orders.php"><?= translateText('Go to orders') ?></a>
<form action="products.php" method="post">
    <input type="hidden" name="logout" value="true">
    <button type="submit"><?= translateText('Logout') ?></button>
</form>
<?php

die();
?>
</body>
</html>
