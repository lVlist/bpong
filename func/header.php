<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/conf/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/conf/dbconfig.php');

$year = $conn->query("SELECT YEAR(date) as year FROM $dbt_games ORDER BY date DESC LIMIT 1") -> fetch_assoc();
$year = $year['year'];

function menu()
{
    global $organization, $title, $year;
    $login = getUserLogin();

    echo "
<!DOCTYPE HTML>
<html lang='ru'>
<head>
<title>".$title."</title>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js'></script>
    <script src='../js/search.js?ver=1.1'></script>
    <link rel='shortcut icon' href='http://".$_SERVER['HTTP_HOST']."/css/".$organization."/favicon.ico' type='image/x-icon'/>
    <link href='http://".$_SERVER['HTTP_HOST']."/css/style.css?ver=3.3' rel='stylesheet'>
  <meta charset='utf-8'>
</head>
<header>
<div class='header-container'>
    <div class='logo'>
        <a href='/'><img src='http://".$_SERVER['HTTP_HOST']."/img/".$organization."/logo.svg' alt='logo'></a>
    </div>
    <nav role='navigation'>
            <ul class='main-menu'>
                <li><a href='/'>ГЛАВНАЯ</a></li>                
                <li><a href='/statistics.php?year=".$year."&type=main'>CТАТИСТИКА</a></li>
                <li><a href='/rules.php'>ПРАВИЛА</a></li>";
    if (null === $login) {
        echo "<a href='#openModal'>ВОЙТИ</a>
                    <div id='openModal' class='modalDialog'>
                        <div style='padding: 10px'>
                        <a href='#close' title='Закрыть' class='close'>X</a>                            
                            <form action='http://".$_SERVER['HTTP_HOST']."/func/login.php' method='POST'>
                                <input type='text' name='login' id='login' class='login' placeholder='Пользователь'  autocomplete='off'>                        
                                <input type='password' name='password' id='password' class='login' placeholder='Пароль'  autocomplete='off'>                        
                                <input class='submit' type='submit' value='ВОЙТИ'>
                            </form>
                        </div>
                    </div>";
    }
    echo '</ul>
    </nav>
</div>
</header>';
}

function menuAdmin()
{
    global $year, $organization;
    $login = getUserLogin();



    echo "<div class='admin-container'>
            <ul class='main-menu'>";
    if (null != $login) {
        echo "<li class='admin-li'>ADMIN MENU: </li>";
        echo "<li><a href='http://".$_SERVER['HTTP_HOST'].'/admin/tournaments.php?year='.$year."'>ТУРНИРЫ</a></li>";
        echo "<a href='#openModal'>СОЗДАТЬ ТУРНИР</a>
                        <div id='openModal' class='modalDialog'>
                            <div style='padding: 10px'>
                            <a href='#close' title='Закрыть' class='close'>X</a>                            
                                <form action='../func/edit_game.php' method='POST'>
                                    <input class='input-block' type='text' name='new_game' class='login' placeholder='Название турнира'  autocomplete='off'><br>
                                    <center>
                                                      
                                        <input type='radio' id='check1' name='type' value='sat'><label for='check1'>Суббота </label> |
                                        <input type='radio' id='check2' name='type' value='thu' checked><label for='check2'>Недельный </label> |
                                        <input type='radio' id='check3' name='type' value='";
                                            echo $organization == "minsk"? "old'><label for='check3'>Old school": "personal'><label for='check3'>Личный";
                                        echo "</label><br><br>

                                        
                                        <input type='radio' id='check4' name='type' value='king'><label for='check4'>King </label>
                                        <input type='radio' id='check5' name='type' value='queen'><label for='check5'>Queen </label> |
                                        <input type='radio' id='check6' name='type' value='grand'><label for='check6'>GRAND </label> |  
                                        <input type='radio' id='check7' name='type' value='other'><label for='check7'>Иные </label>
                                        <input type='hidden' name='bronze' value='0'>
                                        <br><br><input type='checkbox' id='box' name='bronze' value='1' ";
                                                    if ($organization == "minsk"){
                                                        echo "checked";
                                                    }
                                                echo "><label for='box'>Матч за 3-е место</label>
                                    </center>
                                    <input class='submit' type='submit' value='СОЗДАТЬ'>
                                </form>
                            </div>
                        </div>";
        echo "<li><a href='http://".$_SERVER['HTTP_HOST']."/admin/teams.php'>КОМАНДЫ</a></li>";
        echo "<li><a href='http://".$_SERVER['HTTP_HOST']."/admin/score.php'>ОЧКИ</a></li>";
        echo "<li><a href='http://".$_SERVER['HTTP_HOST']."/admin/qualification.php'>ТЕКУЩИЙ ТУРНИР</a></li>";
    }
    echo '</ul>
</div>';
}
