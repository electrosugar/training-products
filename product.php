<?php

require_once 'common.php';
checkLogin();

if (isset($_GET['addProduct']) && strip_tags($_GET['addProduct'])) {
    unset($_SESSION['productId']);
    $productToEdit['title'] = '';
    $productToEdit['description'] = '';
    $productToEdit['price'] = '';
}
if (isset($_GET['productId']) && !empty($_GET['productId']) && filter_var($_GET['productId'], FILTER_VALIDATE_INT)) {
    $_SESSION['productId'] = strip_tags($_GET['productId']);
    $selectProduct = $pdoConnection->prepare('SELECT title, description, price FROM products where id = ? ');
    $selectProduct->execute([$_SESSION['productId']]);
    $productToEdit = $selectProduct->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //validation
    $failure = [];
    $missing = [];

    if (strlen($_POST['title']) > 50) {
        $failure['title'] = translateText('Title has to be under 50 characters');
    }
    if (strlen($_POST['description']) > 255) {
        $failure['description'] = translateText('Description has to be under 255 characters');
    }
    if (!empty($_POST['price'])) {
        if (strlen((int)$_POST['price']) > 8) {
            $failure['price'] = translateText('The price cannot be bigger than 8 digits');
        }
        if (!filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT)) {
            if (isset($failure['price'])) {
                $failure['price'] .= translateText('Not a valid number');
            } else {
                $failure['price'] = translateText('Not a valid number');
            }
        }
    }
    if (empty($_POST['title'])) {
        $missingEdit['title'] = translateText('No title uploaded -not modified! ');
    }
    if (empty($_POST['description'])) {
        $missingEdit['description'] = translateText('No description uploaded -not modified! ');
    }
    if (empty($_POST['price'])) {
        $missingEdit['price'] = translateText('No price uploaded -not modified! ');
    }
    if (empty($_FILES['image'])) {
        $missingEdit['image'] = translateText('No image-not modified ');
    }

    if (!empty($_FILES['image']['tmp_name'])) {
        if (mime_content_type($_FILES['image']['tmp_name']) != 'image/png') {
            $failure['imageType'] = translateText('File must be PNG ');

        }
    }

    if ($_FILES['image']['size'] > 20000) {
        $failure['image'] = translateText('File size must be under 2 MB ');
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
                $success = translateText('Successful Edit!');
            } else {
                $failure['edit'] = translateText('Failed Update!');
            }
        } else if (!$_FILES['image']['tmp_name']) {
            $failure['edit'] = translateText('No editing was done! Image upload failed');
        }
        $targetDir = 'images/';
        $targetFile = $targetDir . $productEditId . '.png';
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $successFileUpload = translateText('The file ') . strip_tags(basename($_FILES['image']['name'])) . translateText(' has been uploaded.');
        }
    } else {
        if (empty($_POST['title'])) {
            $missing['title'] = translateText('No title uploaded! ');
        }
        if (empty($_POST['description'])) {
            $missing['description'] = translateText('No description uploaded! ');
        }
        if (empty($_POST['price'])) {
            $missing['price'] = translateText('No price uploaded! ');
        }
        if (empty($_FILES['image'])) {
            $missing['image'] = translateText('No image uploaded! ');
        }

        if (count($updateValues) === 3 && is_uploaded_file($_FILES ['image'] ['tmp_name']) && !$failure) {
            $targetDir = 'images/';
            $addProduct = $pdoConnection->prepare('INSERT INTO products(title, description, price) VALUES (?, ?, ?)');
            if ($addProduct->execute($updateValues)) {
                $success = 'Successful Product Insert!';
            }
            $targetFile = $targetDir . $pdoConnection->lastInsertId() . '.png';
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $successFileUpload = translateText('The file ') . strip_tags(basename($_FILES['image']['name'])) . translateText(' has been uploaded.');
            }
        } else if (!isset($_SESSION['productId']) && !$failure) {
            $failure['insert'] = 'To add an item correctly complete all fields';
        } else if (!$failure) {
            $failure['edit'] = 'Edit failed';
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
    <title><?= translateText('Add or Edit product') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<h1><?= translateText('Welcome ') . $_SESSION['username'] ?> !</h1>
<h2><?= $value = isset($_SESSION['productId']) ? translateText('Editing Product #') . $_SESSION['productId'] : translateText('Creating New Product') ?></h2>
<div class="error"><?= isset($failure['insert']) ? translateText($failure['insert']) : '' ?></div>
<div class="error"><?= isset($failure['edit']) ? translateText($failure['edit']) : '' ?></div>
<div class="success"><?= isset($success) ? translateText($success) : '' ?></div>

<form enctype="multipart/form-data" action="product.php" method="post" class="form">
    <label>
        <?= translateText('Product Title') ?><br>
        <input type="text" name="title" placeholder="<?= translateText('Product Title') ?>"
               value="<?= $value = isset($_POST['title']) ? htmlspecialchars(strip_tags($_POST['title'])) : $productToEdit['title'] ?>">
    </label><br>
    <div class="error"><?= isset($failure['title']) ? translateText($failure['title']) : '' ?></div>
    <div class="error"><?= isset($missing['title']) ? translateText($missing['title']) : '' ?></div>
    <div class="error"><?= isset($missingEdit['title']) ? translateText($missingEdit['title']) : '' ?></div>

    <label>
        <?= translateText('Product Description') ?><br>
        <textarea name="description"
                  placeholder="<?= translateText('Description') ?>"><?= $value = isset($_POST['description']) ? htmlspecialchars(strip_tags($_POST['description'])) : $productToEdit['description'] ?></textarea>
    </label><br>
    <div class="error"><?= $failure['description'] ?? '' ?></div>
    <div class="error"><?= isset($missing['description']) ? translateText($missing['description']) : '' ?></div>
    <div class="error"><?= isset($missingEdit['description']) ? translateText($missingEdit['description']) : '' ?></div>

    <label>
        <?= translateText('Product Price') ?><br>
        <input type="text" name="price" placeholder="<?= translateText('Price') ?> "
               value="<?= $value = isset($_POST['price']) ? htmlspecialchars(strip_tags($_POST['price'])) : $productToEdit['price'] ?>">
    </label><br>
    <div class="error"><?= isset($failure['price']) ? translateText($failure['price']) : '' ?></div>
    <div class="error"><?= isset($missing['price']) ? translateText($missing['price']) : '' ?></div>
    <div class="error"><?= isset($missingEdit['price']) ? translateText($missingEdit['price']) : '' ?></div>

    <input type="file" name="image" value=<?= translateText('Browse') ?>>
    <?php if (isset($_SESSION['productId'])): ?>
        <?= translateText('Current Image : ') ?>
        <img src="images/<?= $value = isset($_SESSION['productId']) ? $_SESSION['productId'] : '' ?>.png"
             alt="<?= $value = isset($_SESSION['productId']) ? $_SESSION['productId'] : '' ?>" height="50px"
             width="50px">
    <?php endif ?>
    <div class="success"><?= isset($successFileUpload) ? translateText($successFileUpload) : '' ?></div>

    <div class="error"><?= isset($failure['image']) ? translateText($failure['image']) : '' ?></div>
    <div class="error"><?= isset($failure['imageType']) ? translateText($failure['imageType']) : '' ?></div>
    <div class="error"><?= isset($missing['image']) ? translateText($missing['image']) : '' ?></div>
    <div class="error"><?= isset($missingEdit['image']) ? translateText($missingEdit['image']) : '' ?></div>

    <span class="formLinks"> <input type="submit" value="<?= translateText('Save') ?>"></span>
</form>
<a href="products.php"><?= translateText('Products') ?></a>

<?php

die();
?>
</body>
</html>

