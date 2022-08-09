<?php
    require_once "commons.php";

    function displayIndex(){
        $productsConnection = getDatabaseConnection();
        session_start();
        $fetchedProducts = fetchProducts($productsConnection, "index");
        echo '<div class="products">';
        if ($fetchedProducts->num_rows > 0) {
            while($row = $fetchedProducts->fetch_assoc()) {
                showProducts($row);

                echo '</div >';
                echo '<a href="?addToCart='.$row["id"].'">Add</a>';

                echo '</div>';
                echo '<br>';
            }
        }
        if(isset($_GET['addToCart'])){
            addProduct($_GET['addToCart']);
        }
        echo '<a href="cart.php">Go to cart</a>';
        echo '<div class="products">';
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
  <?php
    displayIndex();
  die();
  ?>

  </body>
</html>