<?php
require_once('conf/dbconfig.php');
require_once('func/func.php');
require_once('func/header.php');
menu();
menuAdmin();

$login = getUserLogin();

$type = $_GET['type'];
if($_GET['year'] === NULL){
    $year = "";
}else{
    $year = "AND YEAR(G.date) = ".$_GET['year'];
}



echo "<div id='main'>";
if ($login != null){
    
    /* Получаем id команд */
    $statistics = $conn->query("SELECT team, points,
    (IFNULL(wins,0)+IFNULL(wins_over,0)+IFNULL(losses,0)+IFNULL(losses_over,0)) as games,
    wins, losses, wins_over, losses_over,
    (IFNULL(wins,0)+IFNULL(wins_over,0))*100/(IFNULL(wins,0)+IFNULL(wins_over,0)+IFNULL(losses,0)+IFNULL(losses_over,0)) as percent, 
    hit_cups, got_cups, difference_cups, tournaments
FROM(
	SELECT team, 
        SUM(points) as points,
        COUNT(id_team) as tournaments,
        SUM(wins) as wins, 
        SUM(losses) as losses, 
        SUM(wins_over) as wins_over, 
        SUM(losses_over) as losses_over,  
        SUM(hit_cups) as hit_cups, 
        SUM(got_cups) as got_cups, 
        SUM(difference_cups) as difference_cups
	FROM $dbt_statistics  ST
	INNER JOIN $dbt_teams T ON T.id = ST.id_team
    INNER JOIN $dbt_games AS G ON ST.id_game = G.id
	WHERE T.team != 'ХАЛЯВА' AND T.type = '$type' $year
	GROUP BY id_team
) as s
ORDER BY points DESC, percent DESC, difference_cups DESC");

$year_games = $conn->query("SELECT DISTINCT YEAR(date) as date FROM bpm_games ORDER BY date ASC");
    echo "<center>";
    echo "<a href='?type={$_GET['type']}' class='type'>FULL</a> ";
    foreach($year_games as $value){
        echo "<a href='?year={$value['date']}&type={$_GET['type']}' class='type'>{$value['date']}</a> ";
    }
    echo "</center>";

    echo "<center><a href='?year={$_GET['year']}&type=main' class='type'>MAIN</a> ";
    echo "<a href='?year={$_GET['year']}&type=king' class='type'>KING</a> ";
    echo "<a href='?year={$_GET['year']}&type=queen' class='type'>QUEEN</a></center><br>";

    echo "<table>";
    echo "<tr>";
    echo "
    <td>№
    <td>Team</td>
    <td>Point</td>
    <td>Games</td>
    <td>Wins</td>
    <td>Losses</td>
    <td>Wins OT</td>
    <td>Losses OT</td>
    <td>Wins %</td>
    <td>Hit cups</td>
    <td>Got cups</td>
    <td>Cups +/-</td>
    <td>Tournaments</td>
    ";
    echo "</tr>";

    $i=1;
    foreach ($statistics as $value){
    echo "<tr>";
    echo "
    <td align='center'>".$i++."</td>
    <td>".$value['team']."</td>
    <td align='center'>".$value['points']."</td>
    <td align='center'>".$value['games']."</td>
    <td align='center'>".$value['wins']."</td>
    <td align='center'>".$value['losses']."</td>
    <td align='center'>".$value['wins_over']."</td>
    <td align='center'>".$value['losses_over']."</td>
    <td align='center'>".round($value['percent'], 1)."%</td>
    <td align='center'>".$value['hit_cups']."</td>
    <td align='center'>".$value['got_cups']."</td>
    <td align='center'>".$value['difference_cups']."</td>
    <td align='center'>".$value['tournaments']."</td>
    ";
    echo "</tr>";
    }
}
echo "</div>";