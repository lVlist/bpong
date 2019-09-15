<?php
require_once('../conf/dbconfig.php');
require_once('func.php');

$s1 = (int)$_POST['s1'];
$s2 = (int)$_POST['s2'];
$id_match = (int)$_POST['id_match'];
$id_game = (int)$_POST['id_game'];
$round = (int)$_POST['round'];
$id_q1 = (int)$_POST['id_q1'];
$id_q2 = (int)$_POST['id_q2'];

if($_POST['s1']){

    /* Записываем результаты игры */
    if($_POST)
    {
        $stmt = $conn->prepare("UPDATE `$dbt_q_games` SET `s1`=?, `s2`=? WHERE (`id`=?)"); 
        $stmt->bind_param('iii',$s1, $s2, $id_match);
        $stmt->execute();
    }
    //var_dump("UPDATE `$dbt_qualification` SET `r$round`=?, `d$round`=? WHERE (`id_game`=?) AND (`id_team`=?)");die;
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
}

if($_POST['table']){
    $table = $_POST['table'];
    $stmt = $conn->prepare("UPDATE `$dbt_q_games` SET `table`=? WHERE (`id`=?)"); 
    $stmt->bind_param('si',$table, $id_match);
    $stmt->execute();
}
header('Location: ../admin/qualification.php?id='.$id_game);