<?php

require_once 'common.php';
if ((!isset($_SESSION['id']) || !isset($_SESSION['username']))) {
    logout();
}
if (isset($_GET['productId'])) {
    $_SESSION['productId'] = strip_tags($_GET['productId']);
}
$pdoConnection = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updateValues = [];
    $updateMarks = '';
    //strip tas from the post and creates a list of values and a string of ?, used in the prepared update statement
    addUpdateQueryColumns($updateValues, $updateMarks, 'title');
    addUpdateQueryColumns($updateValues, $updateMarks, 'description');
    addUpdateQueryColumns($updateValues, $updateMarks, 'price');
    if (isset($_SESSION['productId'])) {
        $productEditId = $_SESSION['productId'];
        if ($updateMarks) {
            $editProduct = $pdoConnection->prepare('UPDATE products SET ' . $updateMarks . ' where id = ?');
            $updateValues[] = $productEditId;
            if ($editProduct->execute($updateValues)) {
                $success = 'Successful Edit!';
                unset($_SESSION['productId']);
            } else {
                $failure = 'Failed Update!';
            }
        } else {
            $failure = 'No editing was done!';
        }
        $target_dir = 'images/';
        $target_file = $target_dir . $productEditId . '.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $successFileUpload = 'The file ' . strip_tags(basename($_FILES['image']['name'])) . ' has been uploaded.';
        }
    } else if (count($updateValues) === 3 && is_uploaded_file($_FILES ['image'] ['tmp_name'])) {
        $target_dir = 'images/';
        $addProduct = $pdoConnection->prepare('INSERT into products(title, description, price) VALUES (?, ?, ?)');
        $addOldProduct = $pdoConnection->prepare('INSERT into old_products(title, description, price) VALUES (?, ?, ?)');
        if ($addProduct->execute($updateValues)) {
            $success = 'Successful Product Insert!';
        }
        $target_file = $target_dir . $pdoConnection->lastInsertId() . '.png';
        $addOldProduct->execute($updateValues);
        $old_file = $target_dir . $pdoConnection->lastInsertId() . 'OLD.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $successFileUpload = 'The file ' . strip_tags(basename($_FILES['image']['name'])) . ' has been uploaded.';
        }
        copy($target_file, $old_file);
    } else {
        $failure = 'To add an item complete all fields';
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
<h1><?= translateText('Welcome') . $_SESSION['username'] ?> !</h1>
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

