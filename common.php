<?php

require_once 'config.php';

function getDatabaseConnection(){
    if(!isset($pdo)){
        $dsn = 'mysql:host='.SERVER_NAME.';dbname='.DATABASE_NAME.';';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            //to prevent some edge case sql injections
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
    $queryMarks = '?';
    if(isset($_SESSION['cart'])){
        if(count($_SESSION['cart']) === 0){
            $queryMarks = '';
        }elseif (count($_SESSION['cart']) > 1) {
            $queryMarks = $queryMarks . str_repeat(',?', count($_SESSION['cart'])-1);
        }
    }else{
        $queryMarks = '';
    }
switch ($page){
    case 'cart':
        if($queryMarks){
            $selectProducts = 'SELECT * from products where id in ('.$queryMarks.')';
        }
        else {
            $selectProducts = 'SELECT * from products where id != id';
        }
        break;
    case 'index':
        if($queryMarks){
            $selectProducts = 'SELECT * from products where not id in ('.$queryMarks.')';
        }
        else{
            $selectProducts = 'SELECT * from products';
        }
        break;
    case 'all': {
        $selectProducts = 'SELECT * from products';
    }
        break;
    default: {
        $selectProducts = 'SELECT * from products';
    }
}

if($queryMarks){
    $statementSelectProducts = $pdoConnection->prepare($selectProducts);
    //the SESSION cart array can have indexes with no values that crash this function
    $statementSelectProducts->execute(array_values($_SESSION['cart']));
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

function addUpdateQueryColumns(& $updateValues, & $updateColumns, $columnName){
    if(isset($_POST[$columnName]) && !empty($_POST[$columnName])){
        $updateValues[] = $_POST[$columnName];
        if($updateColumns){
            $updateColumns .= ', '.$columnName.' = ?';
        }
        else {
            $updateColumns = $columnName.' = ?';
        }
    }
    return $updateColumns;
}