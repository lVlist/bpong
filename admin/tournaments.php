<?php

require_once '../conf/dbconfig.php';
require_once '../func/func.php';
require_once '../func/header.php';
menu();
menuAdmin();
$login = getUserLogin();
$year = $_GET['year'];

$year_games = $conn->query("SELECT DISTINCT YEAR(date) as date FROM {$dbt_games} ORDER BY date ASC");

    echo '<br/><center>';
    foreach ($year_games as $value) {
        echo "<a href='?year={$value['date']}' class='type'>{$value['date']}</a> ";
    }
    echo '</center>';

echo "<div id='main'>";
    if (null === $login) {
        echo 'Доступ запрещен!';
        die;
    }

    // Четверги
    $games = $conn->query("SELECT * FROM {$dbt_games} WHERE type = 'thu' AND YEAR(date) = {$year}  ORDER BY date DESC");
    if ($games->num_rows > 0) {
        echo "<div class='block-t'>";
        echo '<h3>Четверги:</h3>';
        echo '<table>';
        $i = 1;
        foreach ($games as $value) {
            $date = $value['date'];
            $date = date('d.m.Y', strtotime("{$date}"));
            echo "<tr>
                            <td align='center'>".$i++.'</td>
                            <td>'.$date."</td>
                            <td style='min-width: 200px;'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                            <td><a href='final.php?id=".$value['id']."'>Финал</td>
                            <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                        </tr>";
        }
        echo '</table>';
        echo '</div>';
    }

    // Суббота, KING, QUEEN
    echo "<div class='block-t'>";
        $games = $conn->query("SELECT * FROM {$dbt_games} WHERE type = 'grand' AND YEAR(date) = {$year} ORDER BY date DESC");
        if ($games->num_rows > 0) {
            echo '<h3>GRAND FINAL:</h3>';
            echo '<table>';
            $i = 1;
            foreach ($games as $value) {
                $date = $value['date'];
                $date = date('d.m.Y', strtotime("{$date}"));
                echo "<tr>
                        <td align='center'>".$i++.'</td>
                        <td>'.$date."</td>
                        <td style='min-width: 195px;'><a href='grand.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                        <td>
                        <a href='http://".$_SERVER['HTTP_HOST']."/admin/tournaments.php?year=".date('Y')."&id_game=".$value['id']."#grandDelete'>удалить</a>
                           
                        </td>
                    </tr>";
            }
            echo '</table>';
        }

        $games = $conn->query("SELECT * FROM {$dbt_games} WHERE type = 'sat' AND YEAR(date) = {$year} ORDER BY date DESC");
        if ($games->num_rows > 0) {
            echo '<h3>Субботы:</h3>';
            echo '<table>';
            $i = 1;
            foreach ($games as $value) {
                $date = $value['date'];
                $date = date('d.m.Y', strtotime("{$date}"));
                echo "<tr>
                        <td align='center'>".$i++.'</td>
                        <td>'.$date."</td>
                        <td style='min-width: 200px;'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='final.php?id=".$value['id']."'>Финал</td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                    </tr>";
            }
            echo '</table>';
        }

        $games = $conn->query("SELECT * FROM {$dbt_games} WHERE type = 'king' AND YEAR(date) = {$year} ORDER BY date DESC");
        if ($games->num_rows > 0) {
            echo '<h3>KING:</h3>';
            echo '<table>';
            $i = 1;
            foreach ($games as $value) {
                $date = $value['date'];
                $date = date('d.m.Y', strtotime("{$date}"));
                echo "<tr>
                        <td align='center'>".$i++.'</td>
                        <td>'.$date."</td>
                        <td style='min-width: 200px;'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='final.php?id=".$value['id']."'>Финал</td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                    </tr>";
            }
            echo '</table>';
        }

        $games = $conn->query("SELECT * FROM {$dbt_games} WHERE type = 'queen' AND YEAR(date) = {$year} ORDER BY date DESC");
        if ($games->num_rows > 0) {
            echo '<h3>QUEEN:</h3>';
            echo '<table>';
            $i = 1;
            foreach ($games as $value) {
                $date = $value['date'];
                $date = date('d.m.Y', strtotime("{$date}"));
                echo "<tr>
                        <td align='center'>".$i++.'</td>
                        <td>'.$date."</td>
                        <td style='min-width: 200px;'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='final.php?id=".$value['id']."'>Финал</td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                    </tr>";
            }
            echo '</table>';
        }

        $games = $conn->query("SELECT * FROM {$dbt_games} WHERE type = 'other' AND YEAR(date) = {$year} ORDER BY date DESC");
        if ($games->num_rows > 0) {
            echo '<h3>ДРУГИЕ:</h3>';
            echo '<table>';
            $i = 1;
            foreach ($games as $value) {
                $date = $value['date'];
                $date = date('d.m.Y', strtotime("{$date}"));
                echo "<tr>
                        <td align='center'>".$i++.'</td>
                        <td>'.$date."</td>
                        <td style='min-width: 200px;'><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
                        <td><a href='final.php?id=".$value['id']."'>Финал</td>
                        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
                    </tr>";
            }
            echo '</table>';
        }
    echo '</div>';
echo '</div>';


echo "<div id='grandDelete' class='modalGrand'>
<div><a href='http://".$_SERVER['HTTP_HOST']."/admin/tournaments.php?year=".date('Y')."#close' title='Закрыть' class='close'>x</a>
    <h3>Вы действительно хотите удалить турнир?</h3><br>
        <center><form action='http://".$_SERVER['HTTP_HOST']."/func/edit_game.php' method='POST' style='float: right;'>
                            <input type='hidden' name='del_grand' value='".$_GET['id_game']."'>
                            <input class ='submit -addteam' type='submit' style='font-size: 13pt;' value='удалить'>
                            </form><br><br></center>
</div>
    
</div>";
