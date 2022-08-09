<?php
require_once "commons.php";
$productsConnection = getDatabaseConnection();
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cart</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<?php
    $fetchedProducts = fetchProducts($productsConnection, "cart");
    echo '<div class="products">';
    if(!$fetchedProducts){
        echo "No products in cart! <br>";
    }
    else{
        if ($fetchedProducts->num_rows > 0) {
            while($row = $fetchedProducts->fetch_assoc()) {
                showProducts($row);

                echo '</div >';
                echo '<a href="?removeFromCart='.$row["id"].'">Remove</a>';
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