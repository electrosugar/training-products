<?php

require_once 'common.php';
if ((!isset($_SESSION['id']) || !isset($_SESSION['username']))) {
    logout();
}

if (isset($_GET['addProduct']) && strip_tags($_GET['addProduct'])){
    unset($_SESSION['productId']);
}

if (isset($_GET['productId']) && !empty($_GET['productId']) && filter_var($_GET['productId'], FILTER_VALIDATE_INT)) {
    $_SESSION['productId'] = strip_tags($_GET['productId']);
}

$pdoConnection = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //validation
    $failure='';
    if(strlen($_POST['title']) > 30){
        $failure .= 'Title has to be under 30 characters<br>';
    }
    if (strlen($_POST['description']) > 255){
        $failure .= 'Description has to be under 255 characters <br>';
    }
    if(!empty($_POST['price'])){
        if (strlen((int)$_POST['price']) > 8 || !filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT)){
            $failure .= 'The price cannot be bigger than 8 digits and 2 decimal digits and it needs to be a number <br>';
        }
    }
    if($_FILES['image']['size'] > 20000){
        $failure .= 'File size must be under 2 MB <br>';
    }

    $updateValues = [];
    $updateMarks = '';
    //strip tas from the post and creates a list of values and a string of ?, used in the prepared update statement
    addUpdateQueryColumns($updateValues, $updateMarks, 'title');
    addUpdateQueryColumns($updateValues, $updateMarks, 'description');
    addUpdateQueryColumns($updateValues, $updateMarks, 'price');
    if (isset($_SESSION['productId']) && !$failure) {
        $productEditId = $_SESSION['productId'];
        if ($updateMarks) {
            $editProduct = $pdoConnection->prepare('UPDATE products SET ' . $updateMarks . ' where id = ?');
            $updateValues[] = $productEditId;
            if ($editProduct->execute($updateValues)) {
                $success = 'Successful Edit!';
            } else {
                $failure = 'Failed Update!';
            }
        } else {
            $failure = 'No editing was done!';
        }
        $targetDir = 'images/';
        $targetFile = $targetDir . $productEditId . '.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $successFileUpload = 'The file ' . strip_tags(basename($_FILES['image']['name'])) . ' has been uploaded.';
        }
    } else if (count($updateValues) === 3 && is_uploaded_file($_FILES ['image'] ['tmp_name']) && !$failure) {
        $targetDir = 'images/';
        $addProduct = $pdoConnection->prepare('INSERT into products(title, description, price) VALUES (?, ?, ?)');
        $addOldProduct = $pdoConnection->prepare('INSERT into old_products(title, description, price) VALUES (?, ?, ?)');
        if ($addProduct->execute($updateValues)) {
            $success = 'Successful Product Insert!';
        }
        $targetFile = $targetDir . $pdoConnection->lastInsertId() . '.png';
        $addOldProduct->execute($updateValues);
        $old_file = $targetDir . $pdoConnection->lastInsertId() . 'OLD.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $successFileUpload = 'The file ' . strip_tags(basename($_FILES['image']['name'])) . ' has been uploaded.';
        }
        copy($targetFile, $old_file);
    } else {
        $failure .= 'To add an item complete all fields, otherwise correct errors';
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Add or edit product') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<h1><?= translateText('Welcome ') . $_SESSION['username'] ?> !</h1>
<h2><?= $value = isset($_SESSION['productId']) ? translateText('Editing Product #') . $_SESSION['productId'] : translateText('Creating New Product') ?></h2>
<form enctype="multipart/form-data" action="product.php" method="post" class="form">
    <input type="text" name="title" placeholder="<?= translateText('Product Title') ?>"
           value="<?= $value = isset($_POST['title']) ? strip_tags($_POST['title']) : '' ?>"><br>
    <input type="text" name="description" placeholder="<?= translateText('Description') ?>"
           value="<?= $value = isset($_POST['description']) ? strip_tags($_POST['description']) : '' ?>" id="big"><br>
    <input type="text" name="price" placeholder="<?= translateText('Price') ?> "
           value="<?= $value = isset($_POST['price']) ? strip_tags($_POST['price']) : '' ?>"><br>
    <input type="file" name="image" value=<?= translateText('Browse') ?>><br>
    <span class="formLinks"> <input type="submit" value="Save"></span>
</form>
<a href="products.php"><?= translateText('Products') ?></a>
<?= isset($success) ? translateText($success) : '' ?>
<?= isset($successFileUpload) ? translateText($successFileUpload) : '' ?>
<?= isset($failure) ? translateText($failure) : '' ?>
<?php

die();
?>
</body>
</html>

