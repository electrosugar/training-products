<?php

require_once 'common.php';

if (isset($_SESSION['loggedIn'])) {
    header('location: products.php');
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['username'])) {
        $failure['username'] = 'Please enter username.';
    } else {
        $username = strip_tags($_POST['username']);
    }
    if (empty($_POST['password'])) {
        $failure['password'] = 'Please enter your password.';
    } else {
        $password = strip_tags($_POST['password']);
    }
    $userLogin = $pdoConnection->prepare('SELECT id,username,password FROM users WHERE username= ?');
    if (empty($failure)) {
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
                $failure['login'] = 'Invalid username or password';
            }
        } else {
            $failure['login'] = 'Invalid username or password';
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
        <h1><?= translateText('Login') ?></h1>
        <h4 class="error"><?= isset($failure['login']) ? translateText($failure['login']) : '' ?></h4>
        <input type="text" placeholder="<?= translateText('Username') ?>" name="username" class="loginInputs"
               value="<?= $value = isset($_POST['username']) ? htmlspecialchars(strip_tags($_POST['username'])) : '' ?>">
        <?= isset($userError) ? translateText($userError) : '' ?>
        <h4 class="error"><?= isset($failure['username']) ? translateText($failure['username']) : '' ?></h4>

        <input type="password" placeholder="<?= translateText('Password') ?>" name="password" class="loginInputs"
               value="">
        <h4 class="error"><?= isset($failure['password']) ? translateText($failure['password']) : '' ?></h4>

        <input type="submit" class="loginInputs" value="<?= translateText('Login') ?>">
        <a href="index.php"><?= translateText('Anonymous User') ?></a>
    </form>
</div>
</body>
<?php

die();
?>
</html>
