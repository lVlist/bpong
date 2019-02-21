<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();


echo "<div id='main'>";
if ($login != null){
    echo "<div id='create-block'>";
    echo "Выберите команду:
    <input type='text' name='team' placeholder='Название команды' class='team input-team'  autocomplete='off'>
    <div class='search_hal9va'></div>";
    echo "</div>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";