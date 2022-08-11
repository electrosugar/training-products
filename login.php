<?php

require_once 'common.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(empty(trim($_POST['username']))){
        $userError = 'Please enter username.';
    } else{
        $username = trim($_POST['username']);
    }
    if(empty(trim($_POST['password']))){
        $passwordError = 'Please enter your password.';
    } else{
        $password = trim($_POST['password']);
    }

    $userLoginConnection = getDatabaseConnection();
    $userLogin = $userLoginConnection->prepare('SELECT id,username,password from users where username=?');
    $userLogin->bind_param('s',$username);
    if($userLogin->execute()){
        $users = $userLogin->get_result();
        if($users->num_rows == 1){
            while($user = $users->fetch_assoc()) {
                if (isset($password) && password_verify($password, $user['password'])) {
                    session_start();
                    // Store data in session variables
                    $_SESSION['loggedIn'] = true;
                    $_SESSION['id'] = password_hash($user['id'], PASSWORD_BCRYPT);
                    $_SESSION['username'] = $username;
                    // Redirect user to products page
                    header('location: products.php');
                } else {
                    $loginError = 'Invalid username or password';
                }
            }
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
    <title>Login</title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="loginBody">
    <form class="login" method="post" action="login.php" >
        <h1>Login</h1>
        <input type="text" placeholder="<?= translateText('Username')?>" name="username" class="loginInputs" value="">
        <input type="text" placeholder="<?= translateText('Password')?>" name="password" class="loginInputs" value="">
        <input type="submit" class="loginInputs" value="Login">
        <a href="index.php"><?= translateText('Anonymous User')?></a>
        <?php
            if(isset($userError)){
                echo translateText($userError);
            }
            if(isset($passwordError)){
                echo translateText($passwordError);
            }
            if(isset($loginError)){
                echo translateText($loginError);
            }
        ?>
    </form>
</div>
</body>
</html>
