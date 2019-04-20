<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
echo "<center>Название команды изменяется сразу в появившемся поле с командой, только потом жмем \"Изменить\"<br></center>";
if ($login != null){
    echo "<div id='create-block'>";
        echo "Команда:
        <input type='text' name='team' placeholder='Название команды' class='edit_team input-team' autocomplete='off'>
        <div class='search_teams'></div>";
    echo "</div>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";