<?php

require_once 'common.php';
session_start();
if((!isset($_SESSION['id']) || !isset($_SESSION['username']))){
    logout();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productEditId = $_POST['id'];
    $updateValues = [];
    $updateMarks  = '';
    addUpdateQueryColumns($updateValues, $updateMarks, 'title');
    addUpdateQueryColumns($updateValues, $updateMarks, 'description');
    addUpdateQueryColumns($updateValues, $updateMarks, 'price');
    $pdoConnection = getDatabaseConnection();
    if(isset($productEditId) && $productEditId != 0){
        $editProduct = $pdoConnection->prepare('UPDATE products SET '.$updateMarks.' where id = ?');
        array_push($updateValues, $productEditId);
        if($editProduct->execute($updateValues)){
            echo 'Successful Edit!';
        }
        $target_dir = 'images/';
        $target_file = $target_dir . $productEditId . '.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            echo 'The file '. htmlspecialchars( basename( $_FILES['image']['name'])). ' has been uploaded.';
        }
    }
    else if(count($updateValues)===3 && isset($_FILES['image']) && !empty($_FILES['image'])){
        $addProduct = $pdoConnection->prepare('INSERT into products(title, description, price) VALUES (?, ?, ?)');
        if($addProduct->execute($updateValues)){
            echo 'Successful Product Insert!';
        }
        $target_dir = 'images/';
        $target_file = $target_dir . $pdoConnection->lastInsertId() . '.png';
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo 'The file '. htmlspecialchars( basename( $_FILES['image']['name'])). ' has been uploaded.';
        }
    }
    else{
        echo 'No product to edit, to add an item complete all fields';
    }


}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Product</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<h1>Welcome <?= $_SESSION['username'] ?> !</h1>
<form enctype="multipart/form-data" action="product.php" method="post" class="form">
    <input type="hidden" value="<?= strip_tags($_GET['productId']); ?>" name="id" />
    <input type="text" name="title" placeholder="<?= translateText('Product Title') ?>"><br>
    <input type="text" name="description" placeholder="<?= translateText('Description') ?>" id="big"><br>
    <input type="text" name="price" placeholder="<?= translateText('Price') ?> "><br>
    <input type="file" name="image" value=<?= translateText('Browse') ?>><br>
    <span class="formLinks"> <input type="submit" value="Save"></span>
</form>
<a href="products.php"><?= translateText('Products')?></a>
<?php
die();
?>
</body>
</html>

