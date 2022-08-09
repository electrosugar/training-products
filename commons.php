<?php

require_once "config.php";

    function getDatabaseConnection(){
        $productsConnection = new mysqli(constant("SERVER_NAME"), constant("USER"), constant("PASSWORD"), constant("DATABASE_NAME"));

        if($productsConnection->connect_error){
            die("Connection Failed:" . $productsConnection->connect_error);
        }
        return $productsConnection;
    }

    function getCurrency(){
        return '$';
    }

    function addProduct($productId){
        if(!isset($_SESSION['cart'])){
            $_SESSION['cart'] = array();
        }
        if(!in_array($productId, $_SESSION['cart'])){
            array_push($_SESSION['cart'], $productId);
        }

    }

    function removeProduct($productId){
        if(!isset($_SESSION['cart'])){
            $_SESSION['cart'] = array();
        }
        if (($key = array_search($productId, $_SESSION['cart'])) !== false) {
            unset( $_SESSION['cart'][$key]);
        }

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

        }

        $statementProducts = $productsConnection->prepare($selectProducts);
        $statementProducts->execute();
        $resultedProducts = $statementProducts->get_result();
        return $resultedProducts;
    }

    function showProducts($row){
        echo '<div class="product">';
        echo '<img src="images/'.$row["id"].'.png" alt="'.$row["id"].'-image" height="100px" width="100px">';
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
    }

    function translateText($text, $language='english'){
        return $text;
    }
