<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";    
    if ($login === null){
        echo "Доступ запрещен!";
        die;
    }

    /* Четверги */
    $games = $conn->query("SELECT * FROM $dbt_games WHERE type = 'thu' ORDER BY date DESC");
    if($games->num_rows > 0){
        echo "<div id='block'>";           
            echo "<h3>Четверги:</h3>";
            echo "<table>";
                $i = 1;
                foreach ($games as $value){
                    $date = $value['date'];
                    $date = date("d.m.Y", strtotime("$date"));
                        echo "<tr>
                            <td align='center'>".$i++."</td>
                            <td>".$date."</td>
                            <td width='150px'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                            <td><a href='final.php?id=".$value['id']."'>Финал</td>
                            <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                        </tr>";
                    }
            echo "</table>";
        echo "</div>";
    }
    
    /* Суббота, KING, QUEEN */
    echo "<div id='block'>";
        $games = $conn->query("SELECT * FROM $dbt_games WHERE type = 'sat' ORDER BY date DESC");
        if($games->num_rows > 0){
            echo "<h3>Субботы:</h3>";
            echo "<table>";
            $i = 1;
            foreach ($games as $value){
                $date = $value['date'];
                $date = date("d.m.Y", strtotime("$date"));
                    echo "<tr>
                        <td align='center'>".$i++."</td>
                        <td>".$date."</td>
                        <td width='150px'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='final.php?id=".$value['id']."'>Финал</td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                    </tr>";
            }
            echo "</table>";
        }

        $games = $conn->query("SELECT * FROM $dbt_games WHERE type = 'king' ORDER BY date DESC");
        if($games->num_rows > 0){
            echo "<h3>KING:</h3>";
            echo "<table>";
            $i = 1;
            foreach ($games as $value){
                $date = $value['date'];
                $date = date("d.m.Y", strtotime("$date"));
                    echo "<tr>
                        <td align='center'>".$i++."</td>
                        <td>".$date."</td>
                        <td width='150px'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='final.php?id=".$value['id']."'>Финал</td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                    </tr>";
            }
            echo "</table>";
        }

        $games = $conn->query("SELECT * FROM $dbt_games WHERE type = 'queen' ORDER BY date DESC");
        if($games->num_rows > 0){
            echo "<h3>QUEEN:</h3>";
            echo "<table>";
            $i = 1;
            foreach ($games as $value){
                $date = $value['date'];
                $date = date("d.m.Y", strtotime("$date"));
                    echo "<tr>
                        <td align='center'>".$i++."</td>
                        <td>".$date."</td>
                        <td width='150px'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='final.php?id=".$value['id']."'>Финал</td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                    </tr>";
            }
            echo "</table>";
        }
    echo "</div>";
echo "</div>";