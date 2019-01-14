<?php

function menu(){
$login = getUserLogin();

echo "
<!DOCTYPE HTML>
<head>
<title>Beer Pong Minsk - Бир Понг Минск - Аренда Beer Pong - Турниры по Beer Pong</title>
    <script src='https://code.jquery.com/jquery-3.3.1.min.js'></script>
    <script src='../js/search.js'></script>
    <link rel='shortcut icon' href='http://".$_SERVER['HTTP_HOST']."/css/favicon.ico' type='image/x-icon'/>
    <link href='http://".$_SERVER['HTTP_HOST']."/css/style.css?ver=2.2' rel='stylesheet'>
  <meta charset='utf-8'>
</head>
<header>
<div class='header-container'>
    <div class='logo'>
        <a href='/'><img src='http://".$_SERVER['HTTP_HOST']."/img/logo.png' alt='logo'></a>
    </div>
    <nav role='navigation'>
            <ul class='main-menu'>
                <li><a href='/'>ГЛАВНАЯ</a></li>
                <li><a href='/'>ТУРНИРЫ</a></li>
                <li><a href='/rules.php'>ПРАВИЛА</a></li>";
                if ($login === null){
                    echo "<a href='#openModal'>ВОЙТИ</a>
                    <div id='openModal' class='modalDialog'>
                        <div style='padding: 10px'>
                        <a href='#close' title='Закрыть' class='close'>X</a>                            
                            <form action='func/login.php' method='POST'>
                                <input type='text' name='login' id='login' class='login' placeholder='Пользователь'  autocomplete='off'>                        
                                <input type='password' name='password' id='password' class='login' placeholder='Пароль'  autocomplete='off'>                        
                                <input class='submit' type='submit' value='ВОЙТИ'>
                            </form>
                        </div>
                    </div>";
                }
                echo "</ul>
    </nav>
</div>
</header>";
}

function menuAdmin(){
    $login = getUserLogin();
    echo "<div class='admin-container'>
            <ul class='main-menu'>";
                if ($login != null){
                    echo "<li class='admin-li'>ADMIN MENU: </li>";
                    echo "<li><a href='http://".$_SERVER['HTTP_HOST']."/admin/tournaments.php'>ТУРНИРЫ</a></li>";
                    echo "<a href='#openModal'>СОЗДАТЬ ТУРНИР</a>
                    <div id='openModal' class='modalDialog'>
                        <div style='padding: 10px'>
                        <a href='#close' title='Закрыть' class='close'>X</a>                            
                            <form action='../func/edit_game.php' method='POST'>
                                <input class='input-block' type='text' name='new_game' class='login' placeholder='Название турнира'  autocomplete='off'>                     
                                <input class='submit' type='submit' value='СОЗДАТЬ'>
                            </form>
                        </div>
                    </div>";
                    echo "<li><a href='http://".$_SERVER['HTTP_HOST']."/admin/qualification.php'>ТЕКУЩИЙ ТУРНИР</a></li>";
                }
                echo "</ul>
</div>";
}
