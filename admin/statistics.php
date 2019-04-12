<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if ($login != null){

    $id_game = (int)$_GET['id'];

    if($id_game > 0){

        /* Получаем id команд */
        $statistics = $conn->query("SELECT team, points, wins, losses, wins_over, losses_over,
        (IFNULL(wins,0)+IFNULL(wins_over,0))*100/(IFNULL(wins,0)+IFNULL(wins_over,0)+IFNULL(losses,0)+IFNULL(losses_over,0)) as percent, 
        hit_cups, got_cups, difference_cups
        FROM statistics 
        INNER JOIN teams ON teams.id = statistics.id_team
        WHERE statistics.id_game = $id_game AND teams.team != 'ХАЛЯВА'
        ORDER BY points DESC, percent DESC, difference_cups DESC");

        echo "<table>";
            echo "<tr>";
                echo "
                <td>№
                <td>Team</td>
                <td>Point</td>
                <td>Wins</td>
                <td>Losses</td>
                <td>Wins OT</td>
                <td>Losses OT</td>
                <td>Wins %</td>
                <td>Hit cups</td>
                <td>Got cups</td>
                <td>Cups +/-</td>";
            echo "</tr>";

        $i=1;
        foreach ($statistics as $value){
            echo "<tr>";
                echo "
                <td align='center'>".$i++."</td>
                <td>".$value['team']."</td>
                <td align='center'>".$value['points']."</td>
                <td align='center'>".$value['wins']."</td>
                <td align='center'>".$value['losses']."</td>
                <td align='center'>".$value['wins_over']."</td>
                <td align='center'>".$value['losses_over']."</td>
                <td align='center'>".round($value['percent'], 1)."%</td>
                <td align='center'>".$value['hit_cups']."</td>
                <td align='center'>".$value['got_cups']."</td>
                <td align='center'>".$value['difference_cups']."</td>
                ";
            echo "</tr>";
        }
    }else{
        die;
    }
}else{
    echo "Доступ запрещен!";
}
echo "</div>";