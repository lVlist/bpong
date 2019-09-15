<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

if ($login != null){
    
    //получения id игры методом GET если нету последний id из БД
    $id_last_game = $conn -> query ("SELECT G.id FROM $dbt_games G ORDER BY G.id DESC LIMIT 1");
    $last_game = $id_last_game->fetch_assoc();

    if (isset($_GET['id'])){
		$id_game = (int)$_GET['id'];
    }else{
        $id_game = (int)$last_game['id'];
    }

    if($id_game > 0){

        /* Общая таблица 3 туров */
        $qualification = 
            $conn->query("SELECT T.team, Q.r1, Q.r2, Q.r3, Q.result, Q.difference, G.game 
            FROM $dbt_qualification Q
            INNER JOIN $dbt_teams T ON T.id = Q.id_team 
            INNER JOIN $dbt_games G ON G.id = Q.id_game
            WHERE Q.id_game = $id_game
            ORDER BY Q.result DESC, Q.difference DESC");

        /* Получаем название турнира */
        $game = $qualification->fetch_assoc();
        $game = $game['game'];

        echo "<div id='main'>";

        /* Общая таблица 3 туров */
        echo "<div id='block'>";
        echo "<table>";
            echo "<h3>Турнир: ".$game."  <a href='create.php?id=".$id_game."'><img width='16px' src='http://".$_SERVER['HTTP_HOST']."/img/edit.png'></a></h3>";
            echo "<tr align ='center'>";
                echo "  <td>№</td>
                        <td>Команда</td>
                        <td>Тур 1</td>
                        <td>Тур 2</td>
                        <td>Тур 3</td>
                        <td>Итого</td>
                        <td>Разница</td>";
            echo "</tr>";

        $i = 1;
        foreach ($qualification as $value){
            echo "<tr align = 'center'>";
                echo "<td>".$i++."</td>";
                echo "<td>".$value['team']."</td>";
                winLoseView($value['r1']);
                winLoseView($value['r2']);
                winLoseView($value['r3']);
                echo "<td>".$value['result']."</td>";
                echo "<td>".$value['difference']."</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        echo "Выбрать:<a href='limit.php?limit=8&id=$id_game'> 8 |</a>";
        echo "<a href='limit.php?limit=12&id=$id_game'> 12 |</a>";
        echo "<a href='limit.php?limit=16&id=$id_game'> 16 |</a>";
        echo "<a href='limit.php?limit=24&id=$id_game'> 24 |</a>";
        echo "<a href='limit.php?limit=32&id=$id_game'> 32</a> команды для финала<br>";
        echo "</div>";



        /* Выводим 3 тура */
        $sql = "SELECT Q.id AS id_match, t1.team AS t1, Q.s1, Q.s2, t2.team AS t2, Q.round, Q.table, t1.id AS id_t1, t2.id AS id_t2
        FROM $dbt_q_games AS Q
        INNER JOIN $dbt_teams t1 ON t1.id = Q.id_t1
        INNER JOIN $dbt_teams t2 ON t2.id = Q.id_t2
        WHERE Q.id_game = $id_game";

        /* Выборка 3 туров */
        $q_game = $conn->query($sql);

        echo "<div id='block'>";

        for($t=1;$t<=3;$t++){

            //проверяем завершился тур или нет
            foreach ($q_game as $value){
                if($value['round'] == 1 AND $value['s1'] === NULL){
                    $r1 = true;
                    break;
                }elseif($value['round'] == 1 AND $value['s1'] != NULL){
                    $r1 = false;
                }
            }
            foreach ($q_game as $value){
                if($value['round'] == 2 AND $value['s1'] === NULL){
                    $r2 = true;
                    break;
                }elseif($value['round'] == 2 AND $value['s1'] != NULL){
                    $r2 = false;
                }
            }

            //определение порядка вывода туров взависимости от завершения тура
            if ($r1 == true){
                $round = [1 => 1, 2 => 2, 3 => 3];
            }elseif($r2 == false){
                $round = [1 => 3, 2 => 1, 3 => 2];
            }elseif($r1 == false){
                $round = [1 => 2, 2 => 3, 3 => 1];
            }

            //выводим туры
            echo "<h3>Тур ".$round[$t]."</h3>";
            echo "<table>";
            foreach ($q_game as $value){
                if ($value['round'] == $round[$t]){
                echo "<tr>";
                
                    //Название первой команды
                    echo "<td class='tour-td'>".$value['t1']."</td>";

                    //Очки
                    if (!$value['s1']&&!$value['s2']){
                        //запись результатов
                        echo "<td colspan='2' align='center' width='45px'>";
                            edit("<img width='15px' src='http://".$_SERVER['HTTP_HOST']."/img/edit.png'>");
                        echo "</td>";
                    }else{
                        //Очки первой команды
                        if($value['s1']>$value['s2']){
                            echo "<td align='center' class='score-td -color'>";
                                edit($value['s1']);
                            echo "</td>";
                        }else{
                            echo "<td align='center' class='score-td'>";
                                edit($value['s1']);
                            echo "</td>";
                        }
                        //Очки второй команды
                        if($value['s2']>$value['s1']){
                            echo "<td align='center' class='score-td -color'>";
                                edit($value['s2']);
                            echo "</td>";
                        }else{
                            echo "<td align='center' class='score-td'>";
                                edit($value['s2']);
                            echo "</td>";
                        }
                    }

                    //Название второй команды
                    echo "<td align='right' class='tour-td'>".$value['t2']."</td>";
                    
                    //За каким столом играют
                    if($value['s1'] == NULL AND $value['s2'] == NULL){
                        echo "<td>
                            <form action='../func/edit.php' method='POST'>
                                <input type='hidden' name='id_game' value='".$id_game."'>
                                <input type='hidden' name='id_match' value='".$value['id_match']."'>
                                <input type='text' class ='form-table' name='table' value='".$value['table']."'>
                                <input class ='submit -table' type='submit' value='OK'>
                            </form>
                        </td>";
                    }else{
                        echo "<td style='width: 50px; text-align: center;'>".$value['table']."</td>";
                    }
                echo "</tr>";
                }//end if
            }//end foreach
            echo "</table>";
        }//end for
        echo "</div'>";
    }else{
        die;
    }
}else{
    echo "Доступ запрещен!";
}
echo "</div'>";