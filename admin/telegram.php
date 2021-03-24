<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
require_once('../conf/config.php');
menu();
menuAdmin();
$login = getUserLogin();


if ($login != null){
    echo "<br><center>Отправка сообщений в телеграм в группу <b>BEER PONG BELARUS</b><br></center>";
    echo "<div id='main'>";

    echo "<form action='#' method='POST'>
    <textarea name='telegram' rows='10' cols='50'></textarea>
    <center><input class ='submit -addteam' type='submit' value='ОТПРАВИТЬ'></center>";

    if(isset($_POST['telegram'])){
        $message = $_POST['telegram'];
        sendMessage($token, $chatID, $message);
    }

    echo "</div>";
}else{
    echo "Доступ запрещен!";
}