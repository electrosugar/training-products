<?php
require_once "commons.php";
session_start();

$productsConnection = getDatabaseConnection();
$fetchedProducts = fetchProducts($productsConnection, "cart");

    $products = array();
    if (isset($fetchedProducts) && $fetchedProducts->num_rows > 0) {
        while($row = $fetchedProducts->fetch_assoc()) {
            $products[] = $row;
        }
    }

    if(isset($_GET['removeFromCart'])){
        removeProduct($_GET['removeFromCart']);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = htmlspecialchars($_POST['name']);
        $contact = htmlspecialchars($_POST['contact']);
        $comment = htmlspecialchars($_POST['comment']);


        if (!empty($name) && !empty($contact) && !empty($comment)) {
            $insertCustomers = $productsConnection->prepare('INSERT INTO customers (creation_date, name, contact, comment) VALUES (now(), ?, ?, ?)');
            $insertCustomers->bind_param('sss', $name, $contact, $comment);
            $insertCustomers->execute();
            $idCustomer = $insertCustomers->insert_id;

            $insertOrder = $productsConnection->prepare('INSERT INTO orders (id_customer, id_product) VALUES ( ?, ?)');
            foreach($_SESSION['cart'] as $idProduct){
                $insertOrder->bind_param('ii', $idCustomer, $idProduct);
                $insertOrder->execute();
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
    <title>Cart</title>
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
            <span><a href="?removeFromCart=<?php echo htmlspecialchars($product['id']); ?>">Remove</a></span>
        </div>
        <br>
    <?php endforeach ?>
</div>
<form action="cart.php" method="post" class="form">
    <?php echo translateText("Name") ?>: <input type="text" name="name" placeholder="Name"><br>
    <?php echo translateText('Contact Details') ?> <input type="text" name="contact" placeholder="Contact Details"><br>
    <?php echo translateText('Comment') ?> <input type="text" name="comment" placeholder="Comment" id="big"><br>
    <span class="formLinks"> <input type="submit" name="Checkout"><a href="index.php">Go to index</a></span>
</form>
<?php
    die();
?>
</body>
</html>