<?php

require_once 'config.php';
session_start();

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
function getProductsArray($pdoConnection, $selectProducts, $queryMarks = '')
{
    if ($queryMarks) {
        $statementSelectProducts = $pdoConnection->prepare($selectProducts);
        $statementSelectProducts->execute(array_keys($_SESSION['cart']));
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

function addUpdateQueryColumns(& $updateValues, & $updateColumns, $columnName)
{
    if (isset($_POST[$columnName]) && !empty($_POST[$columnName])) {
        //stripping tags from inputs
        $updateValues[] = strip_tags($_POST[$columnName]);
        if ($updateColumns) {
            $updateColumns .= ', ' . $columnName . ' = ?';
        } else {
            $updateColumns = $columnName . ' = ?';
        }
    }
    return $updateColumns;
}

function fetchOrderStatement()
{
    $pdoConnection = getDatabaseConnection();

    $selectOrder = $pdoConnection->prepare('SELECT   OP.id_order,
                                                               O.id,
                                                               O.name,
                                                               O.contact,
                                                               O.comment,
                                                               O.creation_date,
                                                               GROUP_CONCAT(COALESCE(P.title, \'NULL\') SEPARATOR \',\') AS titles,
                                                               GROUP_CONCAT(COALESCE(P.description, \'NULL\') SEPARATOR \',\') AS descriptions,
                                                               GROUP_CONCAT(OP.price SEPARATOR \',\') AS prices,
                                                               GROUP_CONCAT(OP.quantity SEPARATOR \',\') AS quantities,
                                                               SUM(OP.quantity * OP.price) AS totalPrice,
                                                               GROUP_CONCAT(COALESCE(OP.id_product, \'NULL\') SEPARATOR \',\') AS product_ids FROM products P
                                                               RIGHT OUTER JOIN order_product OP ON OP.id_product = P.id  INNER JOIN orders O ON O.id = OP.id_order WHERE O.id = ? GROUP BY OP.id_order');
    return $selectOrder;
}

function orderToArray($row)
{
    $order['id'] = $row['id'];
    $order['name'] = $row['name'];
    $order['contact'] = $row['contact'];
    $order['comment'] = $row['comment'];
    $order['creation_date'] = $row['creation_date'];

    $titles = explode(',', $row['titles']);
    $descriptions = explode(',', $row['descriptions']);
    $prices = explode(',', $row['prices']);
    $quantities = explode(',', $row['quantities']);
    $productId = explode(',', $row['product_ids']);

    $productArray = [];
    $order['productArray'] = [];
    foreach ($productId as $key => $id) {
        $productArray['id'] = $id;
        $productArray['title'] = $titles[$key];
        $productArray['description'] = $descriptions[$key];
        $productArray['price'] = $prices[$key];
        $productArray['quantity'] = $quantities[$key];
        $order['productArray'][] = $productArray;
    }

    $order['totalPrice'] = $row['totalPrice'];

    return $order;
}

function redirect404(){
    http_response_code(404);
    include('404.php');
    die();
}
