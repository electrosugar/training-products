<?php
require_once "commons.php";
$products_connection = getDatabaseConnection();
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Products</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<?php
$fetched_products = fetchProducts($products_connection, "all");
    echo '<div class="products">';
    if(!$fetched_products){
        echo "No products in cart! <br>";
    }
    else{
        if ($fetched_products->num_rows > 0) {
            while($row = $fetched_products->fetch_assoc()) {
                showProducts($row);

                echo '</div >';
                echo '<a href="?deleteProduct='.$row["id"].'">Delete</a>';
                echo '</div>';
                echo '<br>';
            }
        }
    }
    echo '<div class="products">';

    if(isset($_GET['removeFromCart'])){
        removeProduct($_GET['removeFromCart']);
    }

?>
<form action="cart.php" method="post" class="form">
    <?php echo translateText("Name") ?>: <input type="text" name="name" placeholder="Name"><br>
    <?php echo translateText('Contact Details') ?> <input type="text" name="contact" placeholder="Contact Details"><br>
    <?php echo translateText('Comments') ?> <input type="text" name="comments" placeholder="Comments" id="big"><br>
    <span class="formLinks"> <input type="submit" name="Checkout"><a href="index.php">Go to index</a></span>
</form>
<?php
die();
?>
</body>
</html>
