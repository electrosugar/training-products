<?php

require_once 'config.php';

function getDatabaseConnection(){
    if(!isset($pdo)){
        $dsn = 'mysql:host='.SERVER_NAME.';dbname='.DATABASE_NAME.';';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
             $pdo = new PDO($dsn, USER, PASSWORD, $options);
             return $pdo;
        } catch (\PDOException $e) {
             throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
}

function getCurrency(){
    return '$';
}


//fetches specific products, where page specifies the select type for each page(all, cart or index)
//cart = all cart products, index = all products not in the cart
function fetchProducts($pdoConnection, $page){
    $querryValues = '?';
    if(isset($_SESSION['cart'])){
        if(count($_SESSION['cart']) === 0){
            $querryValues = false;
        }elseif (count($_SESSION['cart']) > 1) {
            $querryValues = $querryValues . str_repeat(',?', count($_SESSION['cart'])-1);
        }
    }
switch ($page){
    case 'cart':
        if($querryValues){
            $selectProducts = 'SELECT * from products where id in ('.$querryValues.')';
        }
        else  $selectProducts = 'SELECT * from products where id != id';
        break;
    case 'index':
        if($querryValues){
            $selectProducts = 'SELECT * from products where not id in ('.$querryValues.')';
        }
        else{
            $selectProducts = 'SELECT * from products';
        }
        break;
    case 'all': $selectProducts = 'SELECT * from products';
        break;
    default: $selectProducts = 'SELECT * from products';
}

if($querryValues){
    $statementSelectProducts = $pdoConnection->prepare($selectProducts);
    $statementSelectProducts->execute([...$_SESSION['cart']]);
}
else{
    $statementSelectProducts = $pdoConnection->query($selectProducts);
}
$fetchedProducts = $statementSelectProducts->fetchAll();
return $fetchedProducts;
}

//the page parameter is used to denote what kind of select indexes are fetched (in cart, in index, all)
function getProducts($page){
    $pdoConnection = getDatabaseConnection();
    $fetchedProducts = fetchProducts($pdoConnection, $page);
    $products = [];
    if (isset($fetchedProducts) && $fetchedProducts) {
        foreach($fetchedProducts as $fetchedProduct) {
            array_push($products,$fetchedProduct);
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
