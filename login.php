<?php
    require_once "commons.php";
    $productsConnection = getDatabaseConnection();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="loginBody">
    <form class="login" method="post" action="login.php" >
        Login
        <input type="text" placeholder="Username" name="username" class="loginInputs">
        <input type="text" placeholder="Password" name="password" class="loginInputs">
        <input type="submit" class="loginInputs" value="Login">
    </form>
</div>
</body>
</html>
