<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if($login != null){

    $limit = (int)$_GET['limit'];
    $id_game = (int)$_GET['id'];

    if($limit > 0 AND $id_game > 0){

        $stmt= $conn->query("SELECT T.team, T.id, Q.result, Q.difference 
        FROM $dbt_qualification Q
        INNER JOIN $dbt_teams T ON T.id = Q.id_team
        WHERE Q.id_game = $id_game
        ORDER BY Q.result DESC, Q.difference DESC");
    
        echo "<div id='block'>";
            if($_GET['msg']){
                echo "<p style='color:red'>Было выбранно ".$_GET['msg']." команд из ".$_GET['limit']."</p>";
            }

            if($limit === 12 OR $limit === 24){
                echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/final2.php?id=".$id_game."&limit=".$limit."&final=3' method='POST'>";
            }else{
                echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/final.php?id=".$id_game."&limit=".$limit."&final=3' method='POST'>";
            }

            echo "<input class ='submit -addteam -del' type='submit' value='Начать финал'>";    
            echo "<table>";
            echo "<tr align='center'>
                    <td>№</td>
                    <td>Команда</td>
                    <td>Итого</td>
                    <td>Разница</td>
                    <td>Выборка</td>
                  </tr>";

            $i=0;
            foreach ($stmt as $value){
                $i++;
                if($i <= $_GET['limit']){
                    echo "<tr>
                            <td align='center'>".$i."</td>
                            <td style='min-width: 200px;'>".$value['team']."</td>
                            <td align='center'>".$value['result']."</td>
                            <td align='center'>".$value['difference']."</td>
                            <td align='center'><input type='checkbox' name='limit_val$i' value='".$value['id']."' checked='checked'></td>
                        </tr>";
                }else{
                    echo "<tr>
                            <td align='center'>".$i."</td>
                            <td style='min-width: 200px;'>".$value['team']."</td>
                            <td align='center'>".$value['result']."</td>
                            <td align='center'>".$value['difference']."</td>
                            <td align='center'><input type='checkbox' name='limit_val$i' value='".$value['id']."'></td>
                        </tr>";
                }
            }
            echo "</table>";
            echo "<input class ='submit -addteam -del' type='submit' value='Начать финал'>";
            echo "</form>";
        echo "</div>";
    }else{
        die;
    }
}else{
    echo "Доступ запрещен!";
}
echo "</div'>";