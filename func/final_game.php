<?php
require('../conf/dbconfig.php');
$id_game = $_GET['id'];

/* Записываем резульаты игры */
$s1 = (int)$_POST['s1'];
$s2 = (int)$_POST['s2'];
$id_match = (int)$_POST['id_match'];
$id_game = (int)$_POST['id_game'];
$id_t1 = (int)$_POST['id_t1'];
$id_t2 = (int)$_POST['id_t2'];
$block = (int)$_POST['block'];
$next_block = (int)$_POST['next_block'];
$round = (int)$_POST['round'];
$next_round = $round+1;

if($_POST['id_match'])
{
    /* Записываем счет за игру */
    $stmt = $conn->prepare("UPDATE `final` SET `s1`=?, `s2`=? WHERE (`id`=?)"); 
    $stmt->bind_param('iii',$s1, $s2, $id_match);
    $stmt->execute();
    
    /* Получаем id команд играющих в след раунде */
    $final = $conn->query("SELECT final.id_t1, final.id_t2, final.id, final.block FROM final 
    WHERE final.id_game = $id_game AND final.round = $next_round AND final.block = $next_block");
    $final_team = $final->fetch_assoc();

    /* Получаем id команд играющих в финале */
    $mesto3 = $next_round +1;
    $final2 = $conn->query("SELECT final.id_t1, final.id_t2, final.id, final.block FROM final 
    WHERE final.id_game = $id_game AND final.round = $mesto3 AND final.block = $next_block");
    $final_team2 = $final2->fetch_assoc();

    /* Получаем id последнего раунда */
    $id_last_round = $conn -> query ("SELECT final.round FROM final WHERE final.id_game = $id_game ORDER BY final.round DESC LIMIT 1");
    $last_round = $id_last_round->fetch_assoc();

    /* Записываем команду в следующий раунд */
    $stmt = $conn->prepare("UPDATE `final` SET `id_t1`=?, `id_t2`=? WHERE (`id_game`=?) AND (`round`=?) AND (`block`=?)"); 
    $stmt->bind_param('iiiii',$t1, $t2, $id_game, $next_round, $next_block);

    
    /* Определяем и записываем выйгревшею команду в следующий раунд */
    if($round == $last_round['round']-2){ //игра за 3 место и финал
        if($block%2 == 1){          //команда из нечетного блока
            if($s1>$s2){
                /* 3 место */
                $t1 = $id_t2;
                $t2 = $final_team['id_t2'];
                $stmt->execute();
                
                /* Финал */
                $next_round = $next_round+1;
                $t1 = $id_t1;
                $t2 = $final_team2['id_t2'];
                for($i=1;$i<=3;$i++){
                    $stmt->execute();
                    $next_block = $next_block +1;
                }
            }else{
                /* 3 место */
                $t1 = $id_t1;
                $t2 = $final_team['id_t2'];
                $stmt->execute();

                /* Финал */
                $next_round = $next_round+1;
                $t1 = $id_t2;
                $t2 = $final_team2['id_t2'];
                for($i=1;$i<=3;$i++){
                    $stmt->execute();
                    $next_block = $next_block +1;
                }
            }
        }else{                      //команда из четного блока
            if($s1>$s2){
                /* 3 место */
                $t2 = $id_t2;
                $t1 = $final_team['id_t1'];
                $stmt->execute();

                /* Финал */
                $next_round = $next_round+1;
                $t2 = $id_t1;
                $t1 = $final_team2['id_t1'];
                for($i=1;$i<=3;$i++){
                    $stmt->execute();
                    $next_block = $next_block +1;
                }
            }else{
                /* 3 место */
                $t2 = $id_t1;
                $t1 = $final_team['id_t1'];
                $stmt->execute();

                /* Финал */
                $next_round = $next_round+1;
                $t2 = $id_t2;
                $t1 = $final_team2['id_t1'];
                for($i=1;$i<=3;$i++){
                    $stmt->execute();
                    $next_block = $next_block +1;
                }
            }    
        }
        
    }else{ //все остальные раунды
        if($block%2 == 1){          //команда из нечетного блока
            if($s1>$s2){
                $t1 = $id_t1;
                $t2 = $final_team['id_t2'];
            }else{          
                $t1 = $id_t2;
                $t2 = $final_team['id_t2'];
            }
            if($round != $last_round['round']-1){
                $stmt->execute(); 
            }
        }else{                      //команда из четного блока
            if($s1>$s2){
                $t2 = $id_t1;
                $t1 = $final_team['id_t1'];
            }else{
                $t2 = $id_t2;
                $t1 = $final_team['id_t1'];
            }
            if($round != $last_round['round']-1){
                $stmt->execute(); 
            }
        }
    }

    /* Считаем и записываем статистику финала */

    // Команда 1
    $wins = 0; $wins_over = 0; $losses = 0; $losses_over = 0;
    if ($s1 > $s2){if($s1 == 10){$wins=1;}else{$wins_over=1;}}
    if ($s1 < $s2){if($s1 >= 10){$losses_over=1;}else{$losses=1;}}
    $id_team = $id_t1;

    $stmt = $conn->query("SELECT id_game, id_team, round, block FROM statistics_final");

    foreach($stmt as $value){
        if($value['id_game'] == $id_game AND $value['id_team'] == $id_t1 AND $value['round'] == $round AND $value['block'] == $block){
            $conn->query("DELETE FROM `statistics_final` 
            WHERE (`id_game` = $id_game) AND (`id_team` = $id_t1) AND (`round` = $round) AND (`block` = $block)");
            break;
        }
    }

    $conn->query("INSERT INTO statistics_final (id_game, id_team, round, block, wins, losses, wins_over, losses_over) 
    VALUES ($id_game, $id_team, $round, $block, $wins, $losses, $wins_over, $losses_over)");

    // Команда 2
    $wins = 0; $wins_over = 0; $losses = 0; $losses_over = 0;
    if ($s2 > $s1){if($s2 == 10){$wins=1;}else{$wins_over=1;}}
    if ($s2 < $s1){if($s2 >= 10){$losses_over=1;}else{$losses=1;}}
    $id_team = $id_t2;

    $stmt = $conn->query("SELECT id_game, id_team, round, block FROM statistics_final");

    foreach($stmt as $value){
        if($value['id_game'] == $id_game AND $value['id_team'] == $id_t2 AND $value['round'] == $round AND $value['block'] == $block){
            $conn->query("DELETE FROM `statistics_final` 
            WHERE (`id_game` = $id_game) AND (`id_team` = $id_t2) AND (`round` = $round) AND (`block` = $block)");
            break;
        }
    }

    $conn->query("INSERT INTO statistics_final (id_game, id_team, round, block, wins, losses, wins_over, losses_over) 
    VALUES ($id_game, $id_team, $round, $block, $wins, $losses, $wins_over, $losses_over)");
 
    header('Location: ../admin/final.php?id='.$id_game);
}