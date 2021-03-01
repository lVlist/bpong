<?php

// Выводит ячейки W,L,OL,OW
function winLoseView($value)
{
    if (3 == $value) {
        echo "<td style='background-color: #2E8B57'>W</td>";
    } elseif (2 == $value) {
        echo "<td style='background-color: #6bf'>OW</td>";
    } elseif (1 == $value) {
        echo "<td style='background-color: #999'>OL</td>";
    } elseif (is_null($value)) {
        echo '<td></td>';
    } else {
        echo "<td style='background-color: #f66'>L</td>";
    }
}

// Записываем результаты матча
function edit($name)
{
    global $conn, $sql, $value, $id_game;

    // Матчи для таблицы редактиования
    $match = $conn->query($sql);

    if (isset($_GET['id_match'])) {
        $id_match = $_GET['id_match'];
    } else {
        $id_match = $value['id_match'];
    }

    echo "<a href='?id=".$id_game.'&id_match='.$value['id_match']."#match'>".$name."</a>
    <div id='match' class='modalDialog'>
        <div><a href='#close' title='Закрыть' class='close'>x</a>
            <h3>Результаты матча</h3>
            <form action='http://".$_SERVER['HTTP_HOST']."/func/edit.php' method='POST'>
            <table width='300px'>";
    foreach ($match as $val) {
        if ($id_match == $val['id_match']) {
            echo "<input type='hidden' name='id_match' value='".$id_match."'>
                              <input type='hidden' name='id_game'  value='".$id_game."'>
                              <input type='hidden' name='round'  value='".$val['round']."'>
                              <input type='hidden' name='id_q1'  value='".$val['id_t1']."'>
                              <input type='hidden' name='id_q2'  value='".$val['id_t2']."'>
                              <input type='hidden' name='t1'  value='".$val['t1']."'>
                              <input type='hidden' name='t2'  value='".$val['t2']."'>
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

// Логин
function checkAuth($login, $password)
{
    $users = require $_SERVER['DOCUMENT_ROOT'].'/conf/usersDB.php';

    foreach ($users as $user) {
        if ($user['login'] === $login && $user['password'] === $password) {
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

// финал кнопки редактирования
function finalEdit($s, $winLose)
{
    global $conn, $value, $sql, $id_game, $last_round;

    if (isset($_GET['id_match'])) {
        $id_match = $_GET['id_match'];
    } else {
        $id_match = $value['id'];
    }

    if (!$value['s1'] && !$value['s2']) {
        if ($value['round'] == $last_round['round']) {
            echo "<div class='final -score -final'>";
        } else {
            echo "<div class='final -score'>";
        }

        echo "<a href='?id=".$id_game.'&id_match='.$value['id']."#finalEdit'><img width='13px' src='http://".$_SERVER['HTTP_HOST']."/img/edit_score.png'></a>
        </div>";
    } else {
        if (1 == $winLose) {
            if ($value['round'] == $last_round['round']) {
                echo "<div class='final -score -color -final'>";
            } else {
                echo "<div class='final -score -color'>";
            }
        } elseif (2 == $winLose) {
            if ($value['round'] == $last_round['round']) {
                echo "<div class='final -score -final'>";
            } else {
                echo "<div class='final -score'>";
            }
        }

        echo "<a href='?id=".$id_game.'&id_match='.$value['id']."#finalEdit'>".$value[$s].'</a>
        </div>';
    }

    // Матчи для таблицы редактирования
    $final = $conn->query($sql);

    echo "<div id='finalEdit' class='modalDialog'>
        <div><a href='#close' title='Закрыть' class='close'>x</a>
            <h3>Результаты матча</h3>
            <form action='http://".$_SERVER['HTTP_HOST']."/func/final_game.php' method='POST'>
            <table width='300px'>";
    foreach ($final as $val) {
        if ($id_match == $val['id']) {
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

// Окончание слов
function getNumEnding($number, $endingArray)
{
    $number = $number % 100;
    if ($number >= 11 && $number <= 19) {
        $ending = $endingArray[2];
    } else {
        $i = $number % 10;
        switch ($i) {
            case 1: $ending = $endingArray[0];

break;
            case 2:
            case 3:
            case 4: $ending = $endingArray[1];

break;
            default: $ending = $endingArray[2];
        }
    }

    return $ending;
}

// Сравнение строк
function mb_strcasecmp($str1, $str2)
{
    return strcmp(mb_strtoupper($str1, mb_internal_encoding()), mb_strtoupper($str2, mb_internal_encoding()));
}


function sendMessage($messaggio) {

    /*$token = "1608267401:AAGv_Yew28huxjiJR0idzGzajvuX5l2v83U";
    $chatID = "-1001343779648";


    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatID;

    $url = $url . "&text=" . urlencode($messaggio);
    $ch = curl_init();
    $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );

    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;*/
}
