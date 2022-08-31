<?php

require_once 'common.php';
$selectProducts = 'SELECT * FROM products';
$pdoConnection = getDatabaseConnection();
$products = getProductsArray('', $pdoConnection, $selectProducts);


if (isset($_GET['action']) && $_GET['action'] == 'logout' || (!isset($_SESSION['id']) || !isset($_SESSION['username']))) {
    logout();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo $_POST['delete'];
    if (isset($_POST['delete'])) {
        $deleteProduct = $pdoConnection->prepare('DELETE FROM products WHERE id=? LIMIT 1');
        if ($deleteProduct->execute([strip_tags($_POST['delete'])]) === TRUE) {
            $success['delete'] = 'Record deleted successfully!';
        } else {
            $failure['delete'] = 'Failed deleting record!';
        }
        header('Location: products.php');
        die();
    }

    if (isset($_POST['logout'])) {
        unset($_SESSION['username']);
        unset($_SESSION['id']);
        unset($_SESSION['loggedIn']);
        header('Location: index.php');
        die();
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
<?= isset($success['delete']) ? translateText($success['delete']) : '' ?>
<?= isset($failure['delete']) ? translateText($failure['delete']) : '' ?>
<h1> <?= translateText('Welcome back, ') . $_SESSION['username'] ?> !</h1>
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
    <br>
<?php endforeach ?>
<a href="product.php?addProduct=true"><?= translateText('Add Product') ?> </a>

<form action="products.php" method="post">
    <input type="hidden" name="logout" value="true">
    <button type="submit"><?= translateText('Logout') ?></button>
</form>
<a href="?action=logout"><?= translateText('Logout') ?> </a>
<?php

die();
?>
</body>
</html>
