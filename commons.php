<?php

require_once "config.php";

    function getDatabaseConnection(){
        $products_connection = new mysqli(constant("SERVER_NAME"), constant("USER"), constant("PASSWORD"), constant("DATABASE_NAME"));

        if($products_connection->connect_error){
            die("Connection Failed:" . $products_connection->connect_error);
        }
        return $products_connection;
    }

    function getCurrency(){

        return '$';
    }
