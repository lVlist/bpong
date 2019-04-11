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
    <input type='text'  class='change_team input-team' placeholder='Название команды' autocomplete='off'>
    <input type='hidden' id='team' value='".$_POST['edit_team']."'>
    <input type='hidden' id='game' value='".$_POST['id_game']."'>
    <div class='search_change_team'></div>";
    echo "</div>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";