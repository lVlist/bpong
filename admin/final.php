<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
menu();
menuAdmin();
$login = getUserLogin();

//получения id игры методом GET если нету последний id из БД
$id_last_game = $conn -> query ("SELECT games.id FROM games ORDER BY games.id DESC LIMIT 1");
$last_game = $id_last_game->fetch_assoc();
		
if (isset($_GET['id'])){$id_game = (int)$_GET['id'];}else{$id_game = (int)$last_game['id'];}

$sql = "SELECT final.id, t1.team t1, final.s1, final.s2, t2.team t2, final.round, final.block, final.next_block, final.id_t1, final.id_t2
        FROM final
        INNER JOIN teams t1 ON t1.id = IFNULL(final.id_t1, 1)
        INNER JOIN teams t2 ON t2.id = IFNULL(final.id_t2, 1)
        WHERE final.id_game = $id_game";

$final = $conn->query("$sql");

echo "<div id='final'>";
if ($login != null)
{   
    $id_last_round = $conn -> query ("SELECT round FROM final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1");
    $last_round = $id_last_round->fetch_assoc();

    /*echo "<table style='margin:5px;'><tr>";
    echo "<td style='text-align: center'>
    Победитель: 2-ое место: 3-ое место:
    </td>";
    echo "</tr></table>";*/

    echo "<table style='margin:5px;'><tr>";
        for($i=1;$i<=$last_round['round'];$i++)
        {
            if($i == $last_round['round']){
                echo "<td style='width: 188px; text-align: center'>Игра за 3-е место</td>";
            }elseif($i == $last_round['round']-1){
                echo "<td style='width: 188px; text-align: center'>Финал</td>";
            }else{
                echo "<td style='width: 183px; text-align: center'>Раунд {$i}</td>";
            }
        }
    echo "</tr></table>";
    
    for($i=1;$i<=$last_round['round'];$i++)
    {
        
        $e = $i;
        if($i==$last_round['round']){$e = $i-1;}
        if($value['round'] == 1){
            echo "<div id='even".$e."' style='clear: both;'>";
        }else{
            echo "<div id='even".$e."'>";
        }
        if($i==$last_round['round']){
            echo "<div style='margin: 5px 0px'>Игра за 3-е место:</div>";
        }
        
            foreach($final as $value){
                if($value['round'] == $i){
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
        echo "</div>";
    }
}
echo "</div>";