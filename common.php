<?php

require_once 'config.php';

function getDatabaseConnection(){
    $productsConnection = new mysqli(constant('SERVER_NAME'), constant('USER'), constant('PASSWORD'), constant('DATABASE_NAME'));

    if($productsConnection->connect_error){
        die('Connection Failed:' . $productsConnection->connect_error);
    }
    return $productsConnection;
}

function getCurrency(){
    return '$';
}


//fetches specific products, where page specifies the select type for each page(all, cart or index)
//cart = all cart products, index = all products not in the cart
function fetchProducts($productsConnection, $page){
    $cartProducts = '';
    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $cartProduct){
            if($cartProducts){
                $cartProducts = $cartProducts .','.$cartProduct;
            }
            else{
                $cartProducts = $cartProduct;
            }
        }
    }

switch ($page){
    case 'cart':
        if($cartProducts){
            $selectProducts = 'SELECT * from products where id in ('.$cartProducts.')';
        }
        else return null;
        break;
    case 'index':
        if($cartProducts){
            $selectProducts = 'SELECT * from products where not id in ('.$cartProducts.')';
        }
        else{
            $selectProducts = 'SELECT * from products';
        }
        break;
    case 'all': $selectProducts = 'SELECT * from products';
        break;
    default: $selectProducts = 'SELECT * from products';
}

$statementProducts = $productsConnection->prepare($selectProducts);
$statementProducts->execute();
$resultedProducts = $statementProducts->get_result();
return $resultedProducts;
}

//the page parameter is used to denote what kind of select indexes are fetched (in cart, in index, all)
function getProducts($page){
    $productsConnection = getDatabaseConnection();
    $fetchedProducts = fetchProducts($productsConnection, $page);
    $products = array();
    if (isset($fetchedProducts) && $fetchedProducts->num_rows > 0) {
        while($row = $fetchedProducts->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

function translateText($text, $language='english'){
    return $text;
}

function logout(){
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
}
