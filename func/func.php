<?php
/* Шапка */
function menu(){
$login = getUserLogin();

echo "
<!DOCTYPE HTML>
<head>
<title>Beer Pong Minsk - Бир Понг Минск - Аренда Beer Pong - Турниры по Beer Pong</title>
    <script src='https://code.jquery.com/jquery-3.3.1.min.js'></script>
    <script src='../js/search.js'></script>
    <link rel='shortcut icon' href='http://".$_SERVER['HTTP_HOST']."/css/favicon.ico' type='image/x-icon'/>
    <link href='http://".$_SERVER['HTTP_HOST']."/css/style.css?ver=2.1' rel='stylesheet'>
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

/* Выводит ячейки W,L,OL,OW */
function winLoseView($value)
{
    if ($value == 3){echo "<td style='background-color: #2E8B57'>W</td>";}
    elseif ($value == 2){echo "<td style='background-color: #6bf'>OW</td>";}
    elseif ($value == 1){echo "<td style='background-color: #999'>OL</td>";}
    elseif (is_null($value)){echo "<td></td>";}
    else  {echo "<td style='background-color: #f66'>L</td>";}
}

/* Записываем результаты матча */
function edit($name)
{
    global $conn, $sql, $value, $id_game;

    /* Матчи для таблицы редактиования */
    $match = $conn->query($sql);
    
    if (isset($_GET['id_match']))
    {$id_match = $_GET['id_match'];}else{$id_match = $value['id_match'];}
    
    echo "<a href='?id=".$id_game."&id_match=".$value['id_match']."#match'>".$name."</a>
    <div id='match' class='modalDialog'>
        <div><a href='#close' title='Закрыть' class='close'>x</a>
            <h3>Результаты матча</h3>
            <form action='http://".$_SERVER['HTTP_HOST']."/func/edit.php' method='POST'>
            <table width='300px'>";
                foreach ($match as $val)
                {
                    if($id_match == $val['id_match']){
                        echo "<input type='hidden' name='id_match' value='".$id_match."'>
                              <input type='hidden' name='id_game'  value='".$id_game."'>
                              <input type='hidden' name='round'  value='".$val['round']."'>
                              <input type='hidden' name='id_q1'  value='".$val['id_t1']."'>
                              <input type='hidden' name='id_q2'  value='".$val['id_t2']."'>
                        <tr>
                            <td>".$val['t1']."</td>
                            <td width='70px'><input type='number' autocomplete='off' class='form-control -dark ' name='s1' value='".$val['s1']."'></td>
                        </tr>
                        <tr>
                            <td>".$val['t2']."</td>
                            <td width='70px'><input type='number' autocomplete='off' class='form-control -dark ' name='s2' value='".$val['s2']."'></td>
                        </tr>";
                    }
                }
             echo "</table>
             <input class ='submit' type='submit' value='Сохранить'></form>
        </div>
    </div>";
    
}

/* Выводит 3 раунда */
    function roundGame($round)
    {
        global $sql, $conn, $value, $id_game;

        $sql = "SELECT t1.team t1, Q.s1, Q.s2, t2.team t2, Q.round,t1.id AS id_t1, t2.id AS id_t2, Q.id AS id_match 
        FROM q_games Q
        JOIN qualification AS id_t1 ON id_t1.key_team = Q.key_team1
        JOIN qualification AS id_t2 ON id_t2.key_team = Q.key_team2
        JOIN teams t1 ON t1.id = id_t1.id_team
        JOIN teams t2 ON t2.id = id_t2.id_team
        WHERE id_t1.id_game = $id_game AND id_t2.id_game = $id_game AND Q.id_game = $id_game";

        /* Выборка 3 туров */
        $q_game = $conn->query($sql);

        echo "<h3>Тур ".$round."</h3>";
        echo "<table>";
        foreach ($q_game as $value)
        {
            if ($value['round'] == $round)
            {
                echo "<tr>";
                    echo "<td class='tour-td'>".$value['t1']."</td>";
                    if (!$value['s1']&&!$value['s2'])
                    {
                        //если нет результатов запись
                        echo "<td colspan='2' align='center' width='61px'>";
                            edit("<img width='15px' src='http://".$_SERVER['HTTP_HOST']."/img/edit.png'>");
                        echo "</td>";
                    }else{
                        //Очки первой команды
                        if($value['s1']>$value['s2'])
                        {
                            echo "<td align='center' class='score-td -color'>";
                                edit($value['s1']);
                            echo "</td>";
                        }
                        else
                        {
                            echo "<td align='center' class='score-td'>";
                                edit($value['s1']);
                            echo "</td>";
                        }
                        //Очки второй команды
                        if($value['s2']>$value['s1'])
                        {
                            echo "<td align='center' class='score-td -color'>";
                                edit($value['s2']);
                            echo "</td>";
                        }
                        else
                        {
                            echo "<td align='center' class='score-td'>";
                                edit($value['s2']);
                            echo "</td>";
                        }
                    }
                    echo "<td align='right' class='tour-td'>".$value['t2']."</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

/* Логин */
function checkAuth($login,$password)
{
    $users = require __DIR__."/usersDB.php";

    foreach ($users as $user) {
        if ($user['login'] === $login  && $user['password'] === $password) 
        {
            return true;
        }
    }
    return false;
}

function getUserLogin()
{
    $loginFromCookie = $_COOKIE['login'];
    $passwordFromCookie = $_COOKIE['password'];

    if (checkAuth($loginFromCookie, $passwordFromCookie)) {
        return $loginFromCookie;
    }

    return null;
}

function finalEdit($s, $r){
    global $conn, $value, $sql, $r_final2, $id_game;
  
    if (isset($_GET['id_match']))
    {$id_match = $_GET['id_match'];}else{$id_match = $value['id'];}

    if (!$value['s1']&&!$value['s2'])
    {
        echo "<div class='final -score'>
        <a href='?id=".$id_game."&id_match=".$value['id']."#finalEdit'><img width='13px' src='http://".$_SERVER['HTTP_HOST']."/img/edit_score.png'></a>
        </div>";
    }elseif($r == 1){
        echo "<div class='final -score -color'>
        <a href='?id=".$id_game."&id_match=".$value['id']."#finalEdit'>".$value[$s]."</a>
        </div>";
    }elseif($r == 2){
        echo "<div class='final -score'>
        <a href='?id=".$id_game."&id_match=".$value['id']."#finalEdit'>".$value[$s]."</a>
        </div>";
    }

          /* Матчи для таблицы редактиования */
    $final = $conn->query($sql);

    echo "<div id='finalEdit' class='modalDialog'>
        <div><a href='#close' title='Закрыть' class='close'>x</a>
            <h3>Результаты матча</h3>
            <form action='http://".$_SERVER['HTTP_HOST']."/func/final.php' method='POST'>
            <table width='300px'>";
            foreach ($final as $val)
                {
                    if($id_match == $val['id']){
                        echo "<input type='hidden' name='id_match' value='".$id_match."'>
                              <input type='hidden' name='id_game'  value='".$id_game."'>
                              <input type='hidden' name='round'  value='".$val['round']."'>
                              <input type='hidden' name='id_t1'  value='".$val['id_t1']."'>
                              <input type='hidden' name='id_t2'  value='".$val['id_t2']."'>
                              <input type='hidden' name='block'  value='".$val['block']."'>
                              <input type='hidden' name='next_block'  value='".$val['next_block']."'>
                        <tr>
                            <td>".$val['t1']."</td>
                            <td width='70px'><input type='number' autocomplete='off' class='form-control -dark ' name='s1' value='".$val['s1']."'></td>
                        </tr>
                        <tr>
                            <td>".$val['t2']."</td>
                            <td width='70px'><input type='number' autocomplete='off' class='form-control -dark ' name='s2' value='".$val['s2']."'></td>
                        </tr>";
                    }
                }
             echo "</table>
             <input class ='submit' type='submit' value='Сохранить'></form>
        </div>
    </div>";   
}