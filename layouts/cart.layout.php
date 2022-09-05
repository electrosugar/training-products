<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= translateText('Cart') ?></title>
    <?php if ($display) : ?>
        <link rel="stylesheet" href="../stylesheets/index.css">
    <?php else: ?>
        <style>
            body {
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: whitesmoke;
            }

            .products {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
            }

            .orders {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .product {
                padding: 2em;
                margin: 2em;
                line-height: 1.4;
                border-radius: 4px;
                justify-self: center;
                align-self: center;
                display: flex;
                flex-direction: row;
                align-items: center;
                background: ghostwhite;
                box-shadow: 3px 3px 3px #888888, -3px -3px 3px white;
            }

            .roundImage {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                box-shadow: 3px 3px 3px #888888, -3px -3px 3px white;
            }

            .info {
                text-align: left;
                font-family: "Trebuchet MS", sans-serif;
                padding: 1em;
                margin: 1em;
                box-shadow: 3px 3px 3px #888888, -3px -3px 3px white;
            }

            .form {
                display: flex;
                flex-direction: column;

            }

            #big {
                height: 5em;
            }

            .formLinks {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .loginBody {

            }

            .login {
                border: 1em solid black;
                padding: 1em;
                display: flex;
                align-items: center;
                flex-direction: column;
            }

            .loginInputs {
                padding: 1em;
                margin: 1em;
            }

            .order {
                box-shadow: 3px 3px 3px #888888, -3px -3px 3px white;
                background: #a7a6ff;
                margin: 1em;
                display: flex;
                flex-direction: column;
                align-items: center;

            }

            .selectedProducts {
                display: grid;
                grid-template-columns: 1fr;
            }

            .error {
                text-underline: red;
                color: red;
            }

            .success {
                text-underline: forestgreen;
                color: green;
            }
        </style>
    <?php endif; ?>
</head>
<body>
<div id="page">
    <div class="products">
        <?php foreach ($products as $product): ?>
            <div class="product">
                <?php if ($display) : ?>
                    <img src="images/<?= $product['id']; ?>.png" alt="<?= $product['id'] ?>-image"
                         height="100px" width="100px">
                <?php else: ?>
                    <img src="cid:<?= $product['id']; ?>.png" alt="cid:<?= $product['id'] ?>.png-image"
                         height="100px" width="100px">
                <?php endif; ?>
                <div class="info">
                    <span class="title"><?= $product['title'] ?></span>
                    <br>
                    <span class="description"><?= $product['description'] ?></span>
                    <br>
                    <span class="price"><?= $product['price'] . getCurrency() . '*' . $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : '1' ?></span>
                    <span><?= '= ' . $product['price'] * $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : 1 ?></span>
                    <br>
                    <?php if ($display) : ?>
                        <form action="../cart.php" method="post">
                            <label>
                                <?= translateText('Quantity:') ?> <br>
                                <input type="number" name="quantity" min="1"
                                       value="<?= $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : 1 ?>">
                            </label>
                            <div class="error"><?=
                                $value = isset($failure['quantity'][$product['id']]) ? $failure['quantity'][$product['id']] : '' ?></div>
                            <input type="hidden" name="productIdQuantity" value="<?= $product['id'] ?>" min="1">
                            <button type="submit"><?= translateText('Update') ?></button>
                        </form>
                    <?php else: ?>
                        <?= translateText('Quantity:') ?>
                        <div><?= $value = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : 1 ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($display) : ?>
                    <form action="../cart.php" method="post">
                        <input type="hidden" name="remove" value="<?= $product['id'] ?>">
                        <button type="submit"><?= translateText('Remove') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach ?>
    </div>
</div>
<?php if ($display) : ?>
    <form action="../cart.php" method="post" class="form">
        <div class="error"> <?= isset($failure['order']) ? translateText($failure['order']) : '' ?></div>
        <div class="success"> <?= isset($success['checkout']) ? translateText($success['checkout']) : '' ?></div>

        <br>
        <?= translateText('Name') ?> <input type="text" name="name" placeholder="<?= translateText('Name') ?>"
                                            value="<?= $value = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : '' ?>">
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
        <input type="submit" value="<?= translateText('Checkout') ?>">
        <a href="../index.php"><?= translateText('Go to index') ?></a>
    </span>
    </form>
<?php else: ?>
    <?= translateText('Name') ?>
    <div><?= $value = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : '' ?></div>
    <?= translateText('Contact Details') ?>
    <div><?= $value = isset($_POST['contact']) ? htmlspecialchars(strip_tags($_POST['contact'])) : '' ?></div>
    <?= translateText('Comment') ?>
    <div><?= $value = isset($_POST['comment']) ? htmlspecialchars(strip_tags($_POST['comment'])) : '' ?></div>
<?php endif; ?>
</body>
