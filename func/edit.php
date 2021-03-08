<?php
require_once('../conf/dbconfig.php');
require_once('../conf/config.php');
require_once('func.php');

$s1 = (int)$_POST['s1'];
$s2 = (int)$_POST['s2'];
$id_match = (int)$_POST['id_match'];
$id_game = (int)$_POST['id_game'];
$round = (int)$_POST['round'];
$id_q1 = (int)$_POST['id_q1'];
$id_q2 = (int)$_POST['id_q2'];

// получаем статус о telegram
$telegram = (int)$conn->query("SELECT telegram FROM $dbt_games WHERE id = $id_game")->fetch_object()->telegram;

if($_POST['s1'] OR $_POST['s2']){

    /* Записываем результаты игры */
    if($_POST)
    {
        $stmt = $conn->prepare("UPDATE `$dbt_q_games` SET `s1`=?, `s2`=? WHERE (`id`=?)"); 
        $stmt->bind_param('iii',$s1, $s2, $id_match);
        $stmt->execute();
    }

    /* Присваиваем очки за победу/проигрыш */
    function roundUpdate($round)
    {
        global $conn, $dbt_qualification, $s1, $s2, $id_game, $id_q1, $id_q2;
        $stmt = $conn->prepare("UPDATE `$dbt_qualification` SET `r$round`=?, `d$round`=? WHERE (`id_game`=?) AND (`id_team`=?)");
        if( ! $stmt ){ //если ошибка - убиваем процесс и выводим сообщение об ошибке.
            die( "SQL Error: {$conn->errno} - {$conn->error}" );
        }
        $stmt->bind_param('iiii',$r, $d, $id_game, $id_q);
        
        /* Команда 1 */
        if ($s1 > $s2){if($s1 == 10){$r=3;}else{$r=2;}}
        if ($s1 < $s2){if($s1 >= 10){$r=1;}else{$r=0;}}
        $d= $s1-$s2;
        $id_q = $id_q1;
        $stmt->execute();
        /* Команда 2 */
        if ($s2 > $s1){if($s2 == 10){$r=3;}else{$r=2;}}
        if ($s2 < $s1){if($s2 >= 10){$r=1;}else{$r=0;}}
        $d= $s2-$s1;
        $id_q = $id_q2;
        $stmt->execute();
    }
    
    if($_POST)
    {
        roundUpdate($round);
    }
    

    /* Считаем и записываем общее количество очков */
    $stmt = $conn->prepare("UPDATE `$dbt_qualification` SET `result`=?, `difference`=? WHERE (`id_game`=?) AND (`id_team`=?)");
    $stmt->bind_param('iiii',$res, $dif, $id_game, $id_q);

    //считаем очки первой команды
    $result = $conn->query("SELECT Sum(IFNULL(r1,0)+IFNULL(r2,0)+IFNULL(r3,0)) AS result, 
                                Sum(IFNULL(d1,0)+IFNULL(d2,0)+IFNULL(d3,0)) AS difference
                            FROM $dbt_qualification WHERE id_team = $id_q1 AND id_game = $id_game");
        
        
        foreach ($result as $value)
        { 
            $res = (int)$value['result'];
            $dif = (int)$value['difference'];
            $id_q = $id_q1;
            $stmt->execute();
        }

    //считаем очки второй команды
    $result = $conn->query("SELECT Sum(IFNULL(r1,0)+IFNULL(r2,0)+IFNULL(r3,0)) AS result, 
                                Sum(IFNULL(d1,0)+IFNULL(d2,0)+IFNULL(d3,0)) AS difference
                            FROM $dbt_qualification WHERE id_team = $id_q2 AND id_game = $id_game");
        
        foreach ($result as $value)
        {
            $res = (int)$value['result'];
            $dif = (int)$value['difference'];
            $id_q = $id_q2;
            $stmt->execute();
        }

    //Telegram
    if($telegram === 1) {
        $game = $conn->query("SELECT game FROM $dbt_games WHERE id = $id_game")->fetch_object()->game;
        $game_round_check = $conn->query("SELECT ( COUNT(round) - COUNT(s1) ) as game_check 
                    FROM $dbt_q_games WHERE id_game = $id_game AND round = $round")->fetch_object()->game_check;

        //Результаты матча
        $message = "<b>" . $game . " - тур $round </b>\n";
        $message .= $_POST['t1'] . " " . $s1 . ":" . $s2 . " " . $_POST['t2'] . "\n";
        if ($game_round_check > 0) {
            $message .= "до окончания тура " . $game_round_check . getNumEnding($game_round_check, array(' игра', ' игры', ' игр'));
        }
        sendMessage($token, $chatID, $message);

        //Выводим результаты если тур завершен
        $finish_round = $conn->query("SELECT IF( COUNT(round) = COUNT(s1), 1, 0 ) as finish
                    FROM $dbt_q_games WHERE id_game = $id_game AND round = $round")->fetch_object()->finish;


        if ((int)$finish_round == 1) {
            $result_round = $conn->query("SELECT team, result, difference FROM $dbt_qualification Q
                                        INNER JOIN bpm_teams teams ON Q.id_team = teams.id
                                        WHERE id_game = $id_game
                                        ORDER BY result DESC, difference DESC");


            $message = "<b>❗" . $game . " - тур $round завершен❗</b>\n";
            $message .= "M.|O|+/-| Команда \n";
            $i = 1;

            foreach ($result_round as $value) {

                if ($result_round->num_rows > 9 and $i < 10) {
                    $rr = "  ";
                } else {
                    $rr = "";
                }

                if ($value['difference'] > 0) {
                    $pp = "+";
                } elseif ($value['difference'] == 0) {
                    $pp = "  ";
                } else {
                    if (mb_strlen($value['difference']) == 2) {
                        $pp = "   ";
                    } else {
                        $pp = "  ";
                    }
                }

                if (mb_strlen($value['difference']) == 1) {
                    $ddd = "   ";
                } elseif (mb_strlen($value['difference']) == 2) {
                    $ddd = " ";
                } else {
                    $ddd = "";
                }

                $message .= $rr . $i++ . ".|" . $value['result'] . "|" . $ddd . $pp . $value['difference'] . "| " . $value['team'] . "\n";
            }
            sendMessage($token, $chatID, $message);

            //Выводим игры следующего тура
            if ($round == 1 or $round == 2) {
                $commands = $conn->query("SELECT t1.team as t1, t2.team as t2
                                        FROM $dbt_q_games AS Q
                                        INNER JOIN bpm_teams t1 ON t1.id = Q.id_t1
                                        INNER JOIN bpm_teams t2 ON t2.id = Q.id_t2
                                        WHERE id_game = $id_game AND Q.round = " . ($round + 1));

                $message = "<b>❗" . $game . " - игры " . ($round + 1) . " тура:❗</b>\n";
                foreach ($commands as $value) {
                    $message .= "🍻 " . $value['t1'] . " ⚔ " . $value['t2'] . "\n";
                }
                sendMessage($token, $chatID, $message);
            }
        }
    }
}

if($_POST['table']){
    $table = $_POST['table'];
    $stmt = $conn->prepare("UPDATE `$dbt_q_games` SET `table`=? WHERE (`id`=?)"); 
    $stmt->bind_param('si',$table, $id_match);
    $stmt->execute();

    //Telegram
    if($telegram === 1) {
        $message = $_POST['t1'] . " ⚔ " . $_POST['t2'] . "\nиграют за $table столом";
        sendMessage($token, $chatID, $message);
    }
}

$out = json_decode(file_get_contents('php://input'));
if($out[0] == 'del_input'){
    $conn->query("DELETE FROM `{$dbt_final_score}` WHERE `id` = {$out[1]}");
    exit;
}

header('Location: ../admin/qualification.php?id='.$id_game);
exit;