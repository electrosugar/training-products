<?php
    require_once "commons.php";
    $products = getDatabaseConnection();
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
    //TODO add where id in the set of ids not in cart
    $select_products = 'SELECT * from products';
    $statement_products = $products->prepare($select_products);
    $statement_products->execute();
    $resulted_products = $statement_products->get_result();

    if ($resulted_products->num_rows > 0) {
      while($row = $resulted_products->fetch_assoc()) {
          echo '<div class="product">';
          echo '<img src="images/'.$row["id"].'.png" alt="'.$row["id"].'-image">';
          echo '<div class="info">';
          echo '<span class="title">';
          echo $row["title"];
          echo '</span>';
          echo '<br>';

          echo '<span class="description">';
          echo $row["description"];
          echo '</span>';
          echo '<br>';

          echo '<span class="price">';
          echo $row["price"] . getCurrency();
          echo '</span>';
          echo '<br>';

          echo '</div>';
          echo '</div>';
      }
    }
  ?>

  </body>
</html>