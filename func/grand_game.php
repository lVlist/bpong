<?php
require('../conf/dbconfig.php');
$id_match = (int)$_POST['id_match'];
$id_game = (int)$_POST['id_game'];
$id_t1 = (int)$_POST['id_t1'];
$id_t2 = (int)$_POST['id_t2'];
$block = (int)$_POST['block'];
$next_block = substr((float)$_POST['next_block'], 0, 1);
$next_block_position = substr((float)$_POST['next_block'], 1, 1);
$round = (int)$_POST['round'];
$next_round = $round + 1;


if(isset($_POST)){

    /* Записываем счет за игру */
    $stmt = $conn->prepare("INSERT INTO $dbt_grand_score (id_match, id_game, s1, s2) VALUES (?,?,?,?)");
    $stmt->bind_param('iiii', $id_match, $id_game, $s1, $s2);

    $i=0;

    if($_POST['us1'] && $_POST['us2'])
    {
        $conn->query("DELETE FROM `$dbt_grand_score` WHERE (`id_game` = $id_game) AND (`id_match` = $id_match)");
        
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
        foreach ($_POST['is1'] as $value)
        {
            $s1 = $_POST['is1'][$i];
            $s2 = $_POST['is2'][$i];
            $stmt->execute();
            $i++;
        }
    }

    /* Оперделяем кто победил */
    $grand_score = $conn->query("SELECT id, s1, s2 FROM $dbt_grand_score WHERE id_match = $id_match AND id_game = $id_game");

        $s1 = 0; $s2 = 0;
        foreach($grand_score as $gs)
        {
            if($gs['s1']>$gs['s2']){
                $s1 += 1;
            }else{
                $s2 += 1;
            }
        }
    
    
    $stmt = $conn->prepare("UPDATE `$dbt_grand` SET `id_t$next_block_position`=? WHERE (`id_game`=?) AND (`round`=?) AND (`block`=?)"); 
    $stmt->bind_param('iiii',$id_team, $id_game, $next_round, $next_block);

        if($s1>$s2){
            $id_team = $id_t1;
        }else{          
            $id_team = $id_t2;
        }

    $stmt->execute();

    header('Location: ../admin/grand.php?id='.$id_game);
}