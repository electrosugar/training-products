<!DOCTYPE html>
<html>

<head>
    <title><?= translateText('Cart')?></title>
</head>

<body>
<div id="page">
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
                    <form action="../cart.php" method="post">
                        <label>
                            <?= translateText('Quantity:') ?> <br>
                            <input type="number" name="quantity" min="1" value="<?= $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : 1?>">
                        </label>
                        <div class="error"><?=
                            $value = isset($failure['quantity'][$product['id']]) ? $failure['quantity'][$product['id']] : '' ?></div>
                        <input type="hidden" name="productIdQuantity" value="<?= $product['id'] ?>" min="1">
                        <button type="submit"><?= translateText('Update') ?></button>
                    </form>
                </div>

                <form action="../cart.php" method="post">
                    <input type="hidden" name="remove" value="<?= $product['id'] ?>">
                    <button type="submit"><?= translateText('Remove') ?></button>
                </form>
            </div>
        <?php endforeach ?>
    </div>
</div>
<form action="../cart.php" method="post" class="form">
    <div class="error"> <?= isset($failure['order']) ? translateText($failure['order']) : '' ?></div>
    <div class="success"> <?= isset($success['checkout']) ? translateText($success['checkout']) : '' ?></div>

    <br>
    <?= translateText('Name') ?> <input type="text" name="name" placeholder="<?= translateText('Name') ?>"
                                        value="<?= $value = isset($_POST['name']) ?  htmlspecialchars(strip_tags($_POST['name'])) : '' ?>">
    <div class="error"><?= $value = isset($failure['name']) ? $failure['name'] : '' ?></div>
    <br>

    <?= translateText('Contact Details') ?> <input type="text" name="contact"
                                                   placeholder="<?= translateText('Contact Details') ?>"
                                                   value="<?= $value = isset($_POST['contact']) ? htmlspecialchars(strip_tags($_POST['contact'])) : '' ?>">
    <div class="error"><?= $value = isset($failure['contact']) ? $failure['contact'] : '' ?></div>
    <br>


    <label>
        <?= translateText('Comment') ?><br>
        <textarea name="comment"
                  placeholder="<?= translateText('Comment') ?>"><?= $value = isset($_POST['comment']) ? htmlspecialchars(strip_tags($_POST['comment'])) : '' ?></textarea>
    </label><br>
    <div class="error"><?= $value = isset($failure['comment']) ? $failure['comment'] : '' ?></div>
    <br>
    <span class="formLinks">
        <input type="submit" value="Checkout">
        <a href="../index.php"><?= translateText('Go to index') ?></a>
    </span>
</form>
</body>

</html>