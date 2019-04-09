<?php
session_start();
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

$_SESSION['edit_team'] = $_POST['edit_team'];

echo "<div id='main'>";
if ($login != null){
    echo "<div id='create-block'>";
    echo "Выберите команду:
    <input type='text' name='team' placeholder='Название команды' class='team input-team'  autocomplete='off'>
    <div class='search_edit_team'></div>";
    echo "</div>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";