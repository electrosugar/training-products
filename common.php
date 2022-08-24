<?php

require_once 'config.php';
session_start();
$pdoConnection = getDatabaseConnection();

function getDatabaseConnection()
{
    $dsn = 'mysql:host=' . SERVER_NAME . ';dbname=' . DATABASE_NAME . ';';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //to prevent some edge case sql injections
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, USER, PASSWORD, $options);
        return $pdo;
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

function getCurrency()
{
    return '$';
}


function fetchQueryMarks()
{
    $queryMarks = '?';
    if (isset($_SESSION['cart'])) {
        if (count($_SESSION['cart']) === 0) {
            $queryMarks = '';
        } elseif (count($_SESSION['cart']) > 1) {
            $queryMarks = $queryMarks . str_repeat(',?', count($_SESSION['cart']) - 1);
        }
    } else {
        $queryMarks = '';
    }

    return $queryMarks;
}

//fetches and array of products from the supplied query
function getProductsArray($queryMarks, $pdoConnection, $selectProducts)
{
    if ($queryMarks) {
        $statementSelectProducts = $pdoConnection->prepare($selectProducts);
        $statementSelectProducts->execute(array_values($_SESSION['cart']));
    } else {
        $statementSelectProducts = $pdoConnection->query($selectProducts);
    }
    $fetchedProducts = $statementSelectProducts->fetchAll();
    $products = [];
    if (isset($fetchedProducts) && $fetchedProducts) {
        foreach ($fetchedProducts as $fetchedProduct) {
            $products[] = $fetchedProduct;
        }
    }
    $statementSelectProducts = null;
    return $products;
}

function translateText($text, $language = 'english')
{
    return $text;
}

function logout()
{
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    die();
}

function addUpdateQueryColumns(& $updateValues, & $updateColumns, $columnName)
{
    if (isset($_POST[$columnName]) && !empty($_POST[$columnName])) {
        $updateValues[] = $_POST[$columnName];
        if ($updateColumns) {
            $updateColumns .= ', ' . $columnName . ' = ?';
        } else {
            $updateColumns = $columnName . ' = ?';
        }
    }
    return $updateColumns;
}

function prepareOrderWithProducts($row, & $customers)
{
    $databaseConnection = getDatabaseConnection();
    $selectProductIds = $databaseConnection->prepare('select id_product, id_old_product from orders where id_customer = ?');
    if ($selectProductIds) {
        $selectProductIds->execute([$row['id']]);
        $price = 0;
        $productArray = [];
        $productPriceIndex = 0;
        while ($productId = $selectProductIds->fetch()) {
            $selectPrice = $databaseConnection->prepare('select * from old_products where id = ?');
            $selectPrice->execute([$productId['id_old_product']]);
            $productArray[] = $selectPrice->fetch();
            $productArray[$productPriceIndex]['id_product'] = $productId['id_product'];
            $price += $productArray[$productPriceIndex]['price'];
            $productPriceIndex += 1;

        }
        $row['price'] = $price;
        $row['productArray'] = $productArray;
        $customers[] = $row;
    }
    $selectPrice = null;
    $selectProductIds = null;
}