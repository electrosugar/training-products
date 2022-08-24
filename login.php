<?php

require_once 'common.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty(trim($_POST['username']))) {
        $userError = 'Please enter username.';
    } else {
        $username = trim($_POST['username']);
    }
    if (empty(trim($_POST['password']))) {
        $passwordError = 'Please enter your password.';
    } else {
        $password = trim($_POST['password']);
    }

    $userLogin = $pdoConnection->prepare('SELECT id,username,password from users where username= ?');
    if (isset($username) && $userLogin->execute([$username]) && $user = $userLogin->fetch()) {
        if (isset($password) && password_verify($password, $user['password'])) {
            // Store data in session variables
            $_SESSION['loggedIn'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $username;
            // Redirect user to products page
            header('location: products.php');
            die();
        } else {
            $loginError = 'Invalid username or password';
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
    <title><?= translateText('Login') ?></title>
    <link rel="stylesheet" href="stylesheets/index.css">
</head>
<body>
<div class="loginBody">

    <form class="login" method="post" action="login.php">
        <h1>Login</h1>
        <input type="text" placeholder="<?= translateText('Username') ?>" name="username" class="loginInputs"
               value="<?= $value = isset($_POST['username']) ? $_POST['username'] : ''; ?>">
        <input type="password" placeholder="<?= translateText('Password') ?>" name="password" class="loginInputs"
               value="">
        <input type="submit" class="loginInputs" value="Login">
        <a href="index.php"><?= translateText('Anonymous User') ?></a>
        <?= isset($userError) ? translateText($userError) : '' ?>
        <?= isset($passwordError) ? translateText($passwordError) : '' ?>
        <?= isset($loginError) ? translateText($loginError) : '' ?>
    </form>
</div>
</body>
<?php

die();
?>
</html>
