<?php
if (!empty($_POST)) {
    require('func.php');

    $login = $_POST['login'];
    $password = md5($_POST['password']);

    if (checkAuth($login, $password)) {
        setcookie('login', $login, 0, '/');
        setcookie('password', $password, 0, '/');
        header('Location: /index.php');
        exit;
    } else {
        header('Location: /index.php');
        exit;
    }
}
?>