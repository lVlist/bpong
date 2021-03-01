<?php
require('../conf/dbconfig.php');
require('../func/func.php');

$id_match = (int)$_POST['id_match'];
$id_game = (int)$_POST['id_game'];
$id_t1 = (int)$_POST['id_t1'];
$id_t2 = (int)$_POST['id_t2'];
$block = (int)$_POST['block'];
$next_block = (int)$_POST['next_block'];
$next_block_position = (int)$_POST['next_block_position'];
$round = (int)$_POST['round'];
$next_round = $round + 1;
$position = $_POST['position'];

/* Получаем id последнего раунда */
$id_last_round = $conn -> query ("SELECT F.round FROM $dbt_final F
                        WHERE F.id_game = $id_game ORDER BY F.round DESC LIMIT 1");
$last_round = $id_last_round->fetch_assoc();

// получаем статус о игре за 3-е место
$bronze = $conn->query("SELECT bronze FROM $dbt_games WHERE id = $id_game")->fetch_assoc();

$arr_rounds = ["ФИНАЛ", "1/2 финала", "1/4 финала", "1/8 финала", "1/16 финала", "1/32 финала", "1/64 финала"];

if ((int)$bronze == 1){
    $arr_final = ["Финал - игра за 3-е место"];
    for($i = 1; $i <= $last_round['round']-1; $i++){
        array_unshift($arr_final, $arr_rounds[$i-1]);
    }
}else{
    $arr_final = [];
    for($i = 1; $i <= $last_round['round']; $i++){
        array_unshift($arr_final, $arr_rounds[$i-1]);
    }
}

if(isset($_POST)){

    /* Записываем счет за игру */
    $stmt = $conn->prepare("INSERT INTO $dbt_final_score (id_match, id_game, s1, s2) VALUES (?,?,?,?)");
    $stmt->bind_param('iiii', $id_match, $id_game, $s1, $s2);

    

    if($_POST['us1'] && $_POST['us2'])
    {
        
        
        $conn->query("DELETE FROM `$dbt_final_score` WHERE (`id_game` = $id_game) AND (`id_match` = $id_match)");
        $i=0;
        foreach ($_POST['us1'] as $value)
        {
            $s1 = $_POST['us1'][$i];
            $s2 = $_POST['us2'][$i];
            $stmt->execute();
            $i++;
        }
    }
    
    if($_POST['is1'] && $_POST['is2'])
    {
        $i=0;
        foreach ($_POST['is1'] as $value)
        {
            $s1 = $_POST['is1'][$i];
            $s2 = $_POST['is2'][$i];
            $stmt->execute();
            $i++;
        }
    }

    /* Оперделяем кто победил */
    $final_score = $conn->query("SELECT id, s1, s2 FROM $dbt_final_score WHERE id_match = $id_match AND id_game = $id_game");

        $s1 = 0; $s2 = 0;
        foreach($final_score as $gs)
        {
            if($gs['s1']>$gs['s2']){
                $s1 += 1;
            }else{
                $s2 += 1;
            }
        }
    
    
    $stmt = $conn->prepare("UPDATE `$dbt_final` SET `id_t$next_block_position`=? WHERE (`id_game`=?) AND (`round`=?) AND (`block`=?)"); 
    $team_null = 1;

    if((int)$bronze['bronze'] === 1)
    { //игра за 3-е место
        if($round == $last_round['round']-2)
        {
            
            if($s1>$s2){
                $id_team = $id_t1;
            }else{          
                $id_team = $id_t2;
            }

            if(($round != (int)$last_round['round']) && ($round != (int)$last_round['round']-1)){

                if($s1 == $s2){
                    $stmt->bind_param('iiii',$team_null, $id_game, $next_round, $next_block);
                    $stmt->execute();
                }else{
                    $stmt->bind_param('iiii',$id_team, $id_game, $next_round, $next_block);
                    $stmt->execute();
                }
            }
            
            //игра за 3-е место
            if($s1<$s2){
                $id_team = $id_t1;
            }else{          
                $id_team = $id_t2;
            }
            $next_round += 1;
            if(($round != (int)$last_round['round']) && ($round != (int)$last_round['round']-1)){
                if($s1 == $s2){
                    $stmt->bind_param('iiii',$team_null, $id_game, $next_round, $next_block);
                    $stmt->execute();
                }else{
                    $stmt->bind_param('iiii',$id_team, $id_game, $next_round, $next_block);
                    $stmt->execute();
                }
            }

        }
        else
        {
            if($s1>$s2){
                $id_team = $id_t1;
            }else{          
                $id_team = $id_t2;
            }
            if(($round != (int)$last_round['round']) && ($round != (int)$last_round['round']-1)){
                if($s1 == $s2){
                    $stmt->bind_param('iiii',$team_null, $id_game, $next_round, $next_block);
                    $stmt->execute();
                }else{
                    $stmt->bind_param('iiii',$id_team, $id_game, $next_round, $next_block);
                    $stmt->execute();
                }
            }
        }
    }
    else
    { //без игры за 3-е место
        
        if($s1>$s2){
            $id_team = $id_t1;
        }else{          
            $id_team = $id_t2;
        }
        if($round != (int)$last_round['round']){
            if($s1 == $s2){
                $stmt->bind_param('iiii',$team_null, $id_game, $next_round, $next_block);
                $stmt->execute();
            }else{
                $stmt->bind_param('iiii',$id_team, $id_game, $next_round, $next_block);
                $stmt->execute();
            }
        }
    }

    //telegram
    if($_POST['is1']){
        $score1 = $_POST['is1'];
        $score2 = $_POST['is2'];
    }else if($_POST['us1']){
        $score1 = $_POST['us1'];
        $score2 = $_POST['us2'];
    }

    $message = $arr_final[$round-1]."\n";

    for ($i = 0; $i < count($score1); $i++){
        $message .= $_POST['t1']." ".$score1[$i].":".$score2[$i]." ".$_POST['t2']."\n";
    }

    sendMessage($message);
    
    header('Location: ../admin/final.php?id='.$id_game.'#'.$position);
    exit;
}