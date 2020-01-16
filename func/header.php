<?php

function menu()
{
    $login = getUserLogin();

    echo "
<!DOCTYPE HTML>
<html lang='ru'>
<head>
<title>Beer Pong Minsk - Бир Понг Минск - Аренда Beer Pong - Турниры по Beer Pong</title>
    <script src='https://code.jquery.com/jquery-3.3.1.min.js'></script>
    <script src='../js/search.js?ver=3'></script>
    <link rel='shortcut icon' href='http://".$_SERVER['HTTP_HOST']."/css/favicon.ico' type='image/x-icon'/>
    <link href='http://".$_SERVER['HTTP_HOST']."/css/style.css?ver=3.1' rel='stylesheet'>
  <meta charset='utf-8'>
</head>
<header>
<div class='header-container'>
    <div class='logo'>
        <a href='/'><img src='http://".$_SERVER['HTTP_HOST']."/img/logo.svg' width='85px' alt='logo'></a>
    </div>
    <nav role='navigation'>
            <ul class='main-menu'>
                <li><a href='/'>ГЛАВНАЯ</a></li>                
                <li><a href='/statistics.php?year=".date('Y')."&type=main'>CТАТИСТИКА</a></li>
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
    $login = getUserLogin();
    echo "<div class='admin-container'>
            <ul class='main-menu'>";
    if (null != $login) {
        echo "<li class='admin-li'>ADMIN MENU: </li>";
        echo "<li><a href='http://".$_SERVER['HTTP_HOST'].'/admin/tournaments.php?year='.date('Y')."'>ТУРНИРЫ</a></li>";
        echo "<a href='#openModal'>СОЗДАТЬ ТУРНИР</a>
                        <div id='openModal' class='modalDialog'>
                            <div style='padding: 10px'>
                            <a href='#close' title='Закрыть' class='close'>X</a>                            
                                <form action='../func/edit_game.php' method='POST'>
                                    <input class='input-block' type='text' name='new_game' class='login' placeholder='Название турнира'  autocomplete='off'><br>
                                    <center>
                                        <input type='radio' id='check1' name='type' value='grand'><label for='check1'>GRAND </label> |                
                                        <input type='radio' id='check2' name='type' value='sat'><label for='check2'>Суббота </label> |
                                        <input type='radio' id='check3' name='type' value='thu' checked><label for='check3'>Четверг </label><br><br>

                                        
                                        <input type='radio' id='check5' name='type' value='king'><label for='check5'>King </label>
                                        <input type='radio' id='check6' name='type' value='queen'><label for='check6'>Queen </label> |
                                        <input type='radio' id='check4' name='type' value='other'><label for='check4'>Иные </label>
                                        <input type='hidden' name='bronze' value='0'>
                                        <br><br><input type='checkbox' id='box' name='bronze' value='1' checked><label for='box'>Матч за 3-е место</label>
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
