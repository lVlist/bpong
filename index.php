<?php
require_once('conf/dbconfig.php');
require_once('func/func.php');
require_once('func/header.php');
menu();
menuAdmin();

$login = getUserLogin();

echo "<div id='main'>";
if ($login != null){
    /* Получаем id команд */
    $statistics = $conn->query("SELECT d.team, SUM(d.thu) as thu, SUM(d.sat) as sat
FROM (
	SELECT $dbt_teams.team, Count($dbt_statistics.id_team) AS thu, 0 as sat
		FROM $dbt_statistics
		INNER JOIN $dbt_teams ON $dbt_teams.id = $dbt_statistics.id_team
		INNER JOIN $dbt_games ON $dbt_games.id = $dbt_statistics.id_game
		WHERE $dbt_statistics.points = 10 AND $dbt_games.type = 'thu'
		GROUP BY $dbt_statistics.id_team
	UNION ALL
		SELECT $dbt_teams.team, 0 as thu, Count($dbt_statistics.id_team) AS sat
		FROM $dbt_statistics
		INNER JOIN $dbt_teams ON $dbt_teams.id = $dbt_statistics.id_team
		INNER JOIN $dbt_games ON $dbt_games.id = $dbt_statistics.id_game
		WHERE $dbt_statistics.points = 50 AND $dbt_games.type = 'sat'
		GROUP BY $dbt_statistics.id_team) as d
GROUP BY d.team
ORDER BY thu DESC");

    echo "<table>";
    echo "<tr>";
    echo "
    <td>№</td>
    <td>Team</td>
    <td>Четверг</td>
    <td>Суббота</td>
    ";
    echo "</tr>";

    $i=1;foreach ($statistics as $value){
    echo "<tr>";
    echo "
    <td align='center'>".$i++."</td>
    <td>".$value['team']."</td>
    <td align='center'>".$value['thu']."</td>
    <td align='center'>".$value['sat']."</td>
    ";
    echo "</tr>";
    }
}
echo "</div>";