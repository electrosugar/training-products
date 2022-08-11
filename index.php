<?php

require_once 'common.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach($_POST as $productId => $value){
        if($value == translateText('Add')) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (!in_array(strip_tags($productId), $_SESSION['cart'])) {
                array_push($_SESSION['cart'], strip_tags($productId));
            }
            header("Refresh:0");
        }
    }
}
$products = getProducts('index');

?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>Index</title>
      <link rel="stylesheet" href="stylesheets/index.css">
  </head>
  <body>
  <div class="products">
      <?php foreach ($products as $product): ?>
       <div class="product">
          <img src="images/<?= strip_tags($product['id']); ?>.png" alt="'.<?= strip_tags($product['id']); ?>.'-image" height="100px" width="100px">
          <div class="info">
              <span class="title"><?= strip_tags($product['title']); ?></span>
              <br>
              <span class="description"><?= strip_tags($product['description']); ?></span>
              <br>
              <span class="price"><?= strip_tags($product['price'].getCurrency());?></span>
              <br>
              </div >
           <form action="index.php" method="post">
           <input type="submit" name="<?= strip_tags($product['id']); ?>" value="<?= translateText('Add'); ?>">
           </form>
       </div>
      <br>
      <?php endforeach ?>
      <a href="cart.php"><?= translateText('Go to cart'); ?></a>
      <a href="login.php"><?= translateText('Log in')?></a>
  </div>

  </body>
</html>