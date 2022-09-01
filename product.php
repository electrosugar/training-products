<?php

require_once 'common.php';


if (isset($_GET['addProduct']) && strip_tags($_GET['addProduct'])) {
    unset($_SESSION['productId']);
}

if (isset($_GET['productId']) && !empty($_GET['productId']) && filter_var($_GET['productId'], FILTER_VALIDATE_INT)) {
    $_SESSION['productId'] = strip_tags($_GET['productId']);
}


checkLogin();

$pdoConnection = getDatabaseConnection();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //validation
    $failure = [];
    if (strlen($_POST['title']) > 50) {
        $failure['title'] = 'Title has to be under 50 characters<br>';
    }
    if (strlen($_POST['description']) > 255) {
        $failure['description'] = 'Description has to be under 255 characters <br>';
    }
    if (!empty($_POST['price'])) {
        if (strlen((int)$_POST['price']) > 8) {
            $failure['price'] = 'The price cannot be bigger than 8 digits<br>';
        }
        if (!filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT)) {
            if (isset($failure['price'])) {
                $failure['price'] .= 'It has to be a valid number <br>';
            } else {
                $failure['price'] = 'It has to be a valid number <br>';
            }
        }
    }
    if ($_FILES['image']['size'] > 20000) {
        $failure['image'] = 'File size must be under 2 MB <br>';
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
            $editProduct = $pdoConnection->prepare('UPDATE products SET ' . $updateMarks . ' WHERE id = ?');
            $updateValues[] = $productEditId;
            if ($editProduct->execute($updateValues)) {
                $success = 'Successful Edit!';
            } else {
                $failure['edit'] = 'Failed Update!';
            }
        } elseif (!$_FILES['image']['tmp_name']) {
            $failure['edit'] = 'No editing was done!';
        }
        $targetDir = 'images/';
        $targetFile = $targetDir . $productEditId . '.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $successFileUpload = 'The file ' . strip_tags(basename($_FILES['image']['name'])) . ' has been uploaded.';
        }
    } else if (count($updateValues) === 3 && is_uploaded_file($_FILES ['image'] ['tmp_name']) && !$failure) {
        $targetDir = 'images/';
        $addProduct = $pdoConnection->prepare('INSERT INTO products(title, description, price) VALUES (?, ?, ?)');
        if ($addProduct->execute($updateValues)) {
            $success = 'Successful Product Insert!';
        }
        $targetFile = $targetDir . $pdoConnection->lastInsertId() . '.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $successFileUpload = 'The file ' . strip_tags(basename($_FILES['image']['name'])) . ' has been uploaded.';
        }
    } else {
        $failure['insert'] = 'To add an item complete all fields, otherwise correct errors';
        $failure['edit'] = 'Failed Edit';
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Add or Edit product') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<h1><?= translateText('Welcome ') . $_SESSION['username'] ?> !</h1>
<h2><?= $value = isset($_SESSION['productId']) ? translateText('Editing Product #') . $_SESSION['productId'] : translateText('Creating New Product') ?></h2>
<div class="error"><?= isset($failure['insert']) ? translateText($failure['insert']) : '' ?></div>
<div class="error"><?= isset($failure['edit']) ? translateText($failure['edit']) : '' ?></div>
<div class="success"><?= isset($success) ? translateText($success) : '' ?></div>
<div class="success"><?= isset($successFileUpload) ? translateText($successFileUpload) : '' ?></div>

<form enctype="multipart/form-data" action="product.php" method="post" class="form">
    <input type="text" name="title" placeholder="<?= translateText('Product Title') ?>"
           value="<?= $value = isset($_POST['title']) ? $_POST['title'] : '' ?>"><br>
    <div class="error"><?= isset($failure['title']) ? translateText($failure['title']) : '' ?></div>

    <input type="text" name="description" placeholder="<?= translateText('Description') ?>"
           value="<?= $value = isset($_POST['description']) ? $_POST['description'] : '' ?>" id="big"><br>
    <div class="error"><?= isset($failure['description']) ? translateText($failure['description']) : '' ?></div>

    <input type="text" name="price" placeholder="<?= translateText('Price') ?> "
           value="<?= $value = isset($_POST['price']) ? $_POST['price'] : '' ?>"><br>
    <div class="error"><?= isset($failure['price']) ? translateText($failure['price']) : '' ?></div>

    <input type="file" name="image" value=<?= translateText('Browse') ?>><br>
    <div class="error"><?= isset($failure['image']) ? translateText($failure['image']) : '' ?></div>

    <span class="formLinks"> <input type="submit" value="Save"></span>
</form>
<a href="products.php"><?= translateText('Products') ?></a>

<?php

die();
?>
</body>
</html>

