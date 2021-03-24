<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

if ($login != null){
echo "<br><center>Название команды изменяется сразу в появившемся поле с командой, только потом жмем \"Изменить\"<br></center>";
echo "<div id='main'>";


    echo "<div id='create-block'>";
        if(isset($_GET['mes'])){
            if($_GET['mes'] == 'no_del'){
                echo "<p style='color:red'>Команда принимает участие в турнире, сначала удалите её из турнира!</p>";
            }
        }

        echo "Команда:
        <input type='text' name='team' placeholder='Название команды' class='edit_team input-team' autocomplete='off'>
        <div class='search_teams'></div>";
    echo "</div>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";