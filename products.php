<?php

require_once 'common.php';
$selectProducts = 'SELECT * from products';
$products = getProductsArray('', $pdoConnection, $selectProducts);


if (isset($_GET['action']) && $_GET['action'] == 'logout' || (!isset($_SESSION['id']) || !isset($_SESSION['username']))) {
    logout();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST)) {
        $deleteProduct = $pdoConnection->prepare('DELETE FROM products WHERE id=? LIMIT 1');
        if ($deleteProduct->execute([$_POST['delete']]) === TRUE) {
            $success = 'Record deleted successfully!';
        } else {
            $failure = 'Failed deleting record!';
        }
        header('Location: products.php');
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
<?= isset($success) ? translateText($success) : '' ?>
<?= isset($failure) ? translateText($failure) : '' ?>
<h1>Welcome <?= $_SESSION['username'] ?> !</h1>
<?php foreach ($products as $product): ?>
    <div class="product">
        <img src="images/<?= strip_tags($product['id']); ?>.png" alt="<?= strip_tags($product['id']); ?>-image"
             height="100px" width="100px">
        <div class="info">
            <span class="title"><?= strip_tags($product['title']); ?></span>
            <br>
            <span class="description"><?= strip_tags($product['description']); ?></span>
            <br>
            <span class="price"><?= strip_tags($product['price'] . getCurrency()); ?></span>
            <br>
        </div>
        <span>
            <a href="product.php?productId=<?= strip_tags($product['id']); ?>"><?= translateText('Edit Items') ?></a>
            <form action="products.php" method="post">
                <button type="submit" value="<?= strip_tags($product['id']); ?>"
                        name='delete'><?= translateText('Delete') ?></button>
            </form>
        </span>
    </div>
    <br>
<?php endforeach ?>
<a href="product.php">Add product</a>
<a href="?action=logout">Logout</a>
<?php

die();
?>
</body>
</html>
