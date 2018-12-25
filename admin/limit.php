<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
menu();
menuAdmin();
$login = getUserLogin();


echo "<div id='main'>";
if ($login != null)
{
    $limit = $_GET['limit'];
    $id_game = $_GET['id_game'];

    if($_GET)
    {
        $stmt= $conn->query("SELECT teams.team, teams.id, qualification.result, qualification.difference FROM qualification
        INNER JOIN teams ON teams.id = qualification.id_team
        WHERE qualification.id_game = $id_game
        ORDER BY qualification.result DESC, qualification.difference DESC");
    }else{
        die;
    }

echo "<div id='block'>";
    echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/final.php?id=".$id_game."&limit=".$limit."' method='POST'>";
    echo "<input class ='submit -addteam -del' type='submit' value='Выбрать'>";
    
        echo "<table>";
        echo "<tr align='center'>
                <td>№</td>
                <td>Команда</td>
                <td>Итого</td>
                <td>Разница</td>
                <td>Выборка</td>
            </tr>";

        $i=0;foreach ($stmt as $value)
        {
        $i++;
            if($i <= $_GET['limit'])
            {
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
    echo "<input class ='submit -addteam -del' type='submit' value='Выбрать'>";
    echo "</form>";
    echo "</div>";
    }else{
        echo "Доступ запрещен!";
    }

    $stmt= $conn->query("SELECT teams.team, teams.id, qualification.result, qualification.difference FROM qualification
    INNER JOIN teams ON teams.id = qualification.id_team
    WHERE qualification.id_game = $id_game
    ORDER BY qualification.result DESC, qualification.difference DESC LIMIT $limit");

    echo "<div id='block'>";
    $team = '';
    foreach ($stmt as $value){

            $team .= $value['team']."\n";
        }
    $team = substr($team, 0, -2);
echo "<textarea class='input-block' name='teams' rows='40' cols='100'>".$team."</textarea>";
    echo "</div'>";
echo "</div'>";