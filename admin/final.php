<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

//получения id игры методом GET если нету последний id из БД
$id_last_game = $conn -> query ("SELECT games.id FROM games ORDER BY games.id DESC LIMIT 1");
$last_game = $id_last_game->fetch_assoc();
		
if (isset($_GET['id'])){$id_game = (int)$_GET['id'];}else{$id_game = (int)$last_game['id'];}

$sql = "SELECT final.id, t1.team t1, final.s1, final.s2, t2.team t2, final.round, 
        final.block, final.next_block, final.id_t1, final.id_t2 FROM final
        INNER JOIN teams t1 ON t1.id = IFNULL(final.id_t1, 1)
        INNER JOIN teams t2 ON t2.id = IFNULL(final.id_t2, 1)
        WHERE final.id_game = $id_game";
$final = $conn->query("$sql");

echo "<div id='final'>";
if ($login != null){   
    $id_last_round = $conn -> query ("SELECT round, s1, s2, block FROM final 
                                    WHERE id_game = $id_game ORDER BY round DESC LIMIT 1");
    $last_round = $id_last_round->fetch_assoc();

    echo "<table style='margin:5px;'><tr>";
        for($i=1;$i<=$last_round['round'];$i++){
            if($i == $last_round['round']){
                echo "<td style='width: 260px; text-align: center'>Финал</td>";
            }elseif($i == $last_round['round']-1){
                echo "<td style='width: 183px; text-align: center'>Игра за 3-е место</td>";
            }else{
                echo "<td style='width: 183px; text-align: center'>Раунд {$i}</td>";
            }
        }
    echo "</tr></table>";
    
    for($i=1;$i<=$last_round['round']-1;$i++){

        /* Cчетчик стиля id=even */
        if($final->num_rows == 14 OR $final->num_rows == 26){
            if($i >= 2){
                $e = $i-1;
            }else{
                $e = $i;
            }
        }else{
            $e = $i;
        }
        

        echo "<div id='even".$e."'>";

        if($i == $last_round['round']-1){
            echo "<div style='margin: 5px 0px'>Игра за 3-е место:</div>";
        }

        if($i == $last_round['round']){
            $e = 'final';
        }

        foreach($final as $value){
            if($value['round'] == $i){
                if($value['round'] != $last_round['round']){
                    echo "<div class='final -clear'>".$value['t1']."</div>";
                    if($value['s1']>$value['s2']){
                        finalEdit('s1','1');
                    }else{
                        finalEdit('s1','2');
                    }
                    
                    echo "<div class='final -even".$e." -clear'>".$value['t2']."</div>";
                    if($value['s1']<$value['s2']){
                        finalEdit('s2','1');
                    }else{
                        finalEdit('s2','2');
                    }
                }
            }
        }
    echo "</div>";
    }
    
    if($final->num_rows == 10){
        $finalStyle = "final8";
    }elseif($final->num_rows == 14){
        $finalStyle = "final12";
    }elseif($final->num_rows == 18){
        $finalStyle = "final16";
    }elseif($final->num_rows == 26){
        $finalStyle = "final24";
    }elseif($final->num_rows == 34){
        $finalStyle = "final32";
    }


    echo "<div id='".$finalStyle."'>";
    echo "<div style='margin: 5px 0px'>Финал:</div>";
    
    foreach($final as $value){
        if($value['round'] == $last_round['round']){
            if($value['block'] == 1){
                echo "<div class='final -clear'>".$value['t1']."</div>";
                if($value['s1']>$value['s2']){
                    finalEdit('s1','1');
                }else{
                    finalEdit('s1','2');
                }               
            }elseif($value['block'] == 2 || $value['block'] == 3){
                if($value['s1']>$value['s2']){
                    finalEdit('s1','1');
                }else{
                    finalEdit('s1','2');
                }
            }
        }
    }
    
    foreach($final as $value){
        if($value['round'] == $last_round['round']){
            if($value['block'] == 1){
                echo "<div class='final -clear'>".$value['t2']."</div>";
                if($value['s1']<$value['s2']){
                    finalEdit('s2','1');
                }else{
                    finalEdit('s2','2');
                }
            }elseif($value['block'] == 2 || $value['block'] == 3){
                if($value['s1']<$value['s2']){
                    finalEdit('s2','1');
                }else{
                    finalEdit('s2','2');
                }
            }     
        }
    }
    echo "<form action='../func/statistics_game.php' method='POST'>
        <input type='hidden' name='id' value='".$id_game."'>
        <input type='hidden' name='thursday' value='thursday'>
        <input class ='submit -addteam' type='submit' value='Завершить четверг'>
        </form>";
    echo "<form action='../func/statistics_game.php' method='POST'>
        <input type='hidden' name='id' value='".$id_game."'>
        <input type='hidden' name='saturday' value='saturday'>
        <input class ='submit -addteam' type='submit' value='Завершить Субботу'>
        </form>";
    echo "</div>";
}
echo "</div>";