<?php
    require_once "commons.php";
    session_start();

    $productsConnection = getDatabaseConnection();
    $fetchedProducts = fetchProducts($productsConnection, "index");
    $products = array();
    if (isset($fetchedProducts) && $fetchedProducts->num_rows > 0) {
        while($row = $fetchedProducts->fetch_assoc()) {
           $products[] = $row;
        }
    }
    if(isset($_GET['addToCart'])){
        addProduct(htmlspecialchars($_GET['addToCart']));
    }

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
          <img src="images/<?php echo htmlspecialchars($product['id']); ?>.png" alt="'.<?php echo htmlspecialchars($product['id']); ?>.'-image" height="100px" width="100px">
          <div class="info">
              <span class="title"><?php echo htmlspecialchars($product['title']); ?></span>
              <br>
              <span class="description"><?php echo htmlspecialchars($product['description']); ?></span>
              <br>
              <span class="price"><?php echo htmlspecialchars($product['price'].getCurrency());?></span>
              <br>
              </div >
           <span><a href="?addToCart=<?php echo htmlspecialchars($product['id']); ?>">Add</a></span>
       </div>
      <br>
      <?php endforeach ?>
      <a href="cart.php">Go to cart</a>
  </div>

  </body>
</html>