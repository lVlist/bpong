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
    echo "</table>";


    /* ТОП 5 команд main */
    $statistics = $conn->query("SELECT * FROM (	
		SELECT G.date, G.game, 
		IF(s1>s2, T1.team, T2.team) as t1,
		IF(s1>s2, QG.s1, QG.s2) as s1,
		IF(s1>s2, QG.s2, QG.s1) as s2,
		IF(s1>s2, T2.team, T1.team) as t2
		FROM $dbt_q_games QG
		INNER JOIN $dbt_teams T2 ON QG.id_t2 = T2.id 
		INNER JOIN $dbt_teams T1 ON QG.id_t1 = T1.id
		INNER JOIN $dbt_games G ON QG.id_game = G.id
		WHERE (G.type = 'thu' OR G.type = 'sat') AND (QG.s1 > 19 OR QG.s2 > 19)
	UNION ALL
		SELECT G.date, G.game, 
		IF(s1>s2, T1.team, T2.team) as t1,
		IF(s1>s2, S.s1, S.s2) as s1,
		IF(s1>s2, S.s2, S.s1) as s2,
		IF(s1>s2, T2.team, T1.team) as t2
		FROM $dbt_final_score S
		INNER JOIN $dbt_final F ON S.id_match = F.id
		INNER JOIN $dbt_games G ON F.id_game = G.id
		INNER JOIN $dbt_teams T1 ON F.id_t1 = T1.id
		INNER JOIN $dbt_teams T2 ON F.id_t2 = T2.id
		WHERE (G.type = 'thu' OR G.type = 'sat') AND (S.s1 > 19 OR S.s2 > 19)
		) as T
ORDER BY s1 DESC, s2 DESC");
    echo "<br><h3>MAIN</h3>";
    echo "<table>";
        $i=1;foreach ($statistics as $value){
        echo "<tr>";
            echo "
            <td align='center'>".$i++."</td>
            <td>".$value['date']."</td>
            <td align='center'>".$value['game']."</td>
            <td align='center'>".$value['t1']."</td>
            <td align='center'>".$value['s1']."</td>
            <td align='center'>".$value['s2']."</td>
            <td align='center'>".$value['t2']."</td>
            ";
        echo "</tr>";
        }
    echo "</table>";


    /* ТОП 3 king */
    $statistics = $conn->query("SELECT * FROM (	
		SELECT G.date, G.game, 
		IF(s1>s2, T1.team, T2.team) as t1,
		IF(s1>s2, QG.s1, QG.s2) as s1,
		IF(s1>s2, QG.s2, QG.s1) as s2,
		IF(s1>s2, T2.team, T1.team) as t2
		FROM $dbt_q_games QG
		INNER JOIN $dbt_teams T2 ON QG.id_t2 = T2.id 
		INNER JOIN $dbt_teams T1 ON QG.id_t1 = T1.id
		INNER JOIN $dbt_games G ON QG.id_game = G.id
		WHERE G.type = 'king' AND (QG.s1 > 19 OR QG.s2 > 19)
	UNION ALL
		SELECT G.date, G.game, 
		IF(s1>s2, T1.team, T2.team) as t1,
		IF(s1>s2, S.s1, S.s2) as s1,
		IF(s1>s2, S.s2, S.s1) as s2,
		IF(s1>s2, T2.team, T1.team) as t2
		FROM $dbt_final_score S
		INNER JOIN $dbt_final F ON S.id_match = F.id
		INNER JOIN $dbt_games G ON F.id_game = G.id
		INNER JOIN $dbt_teams T1 ON F.id_t1 = T1.id
		INNER JOIN $dbt_teams T2 ON F.id_t2 = T2.id
		WHERE G.type = 'king' AND (S.s1 > 19 OR S.s2 > 19)
		) as T
ORDER BY s1 DESC, s2 DESC");
    echo "<br><h3>KING</h3>";
    echo "<table>";
        $i=1;foreach ($statistics as $value){
        echo "<tr>";
            echo "
            <td align='center'>".$i++."</td>
            <td>".$value['date']."</td>
            <td align='center'>".$value['game']."</td>
            <td align='center'>".$value['t1']."</td>
            <td align='center'>".$value['s1']."</td>
            <td align='center'>".$value['s2']."</td>
            <td align='center'>".$value['t2']."</td>
            ";
        echo "</tr>";
        }
    echo "</table>";


    /* ТОП 3 queen */
    $statistics = $conn->query("SELECT * FROM (	
		SELECT G.date, G.game, 
		IF(s1>s2, T1.team, T2.team) as t1,
		IF(s1>s2, QG.s1, QG.s2) as s1,
		IF(s1>s2, QG.s2, QG.s1) as s2,
		IF(s1>s2, T2.team, T1.team) as t2
		FROM $dbt_q_games QG
		INNER JOIN $dbt_teams T2 ON QG.id_t2 = T2.id 
		INNER JOIN $dbt_teams T1 ON QG.id_t1 = T1.id
		INNER JOIN $dbt_games G ON QG.id_game = G.id
		WHERE G.type = 'queen' AND (QG.s1 > 19 OR QG.s2 > 19)
	UNION ALL
		SELECT G.date, G.game, 
		IF(s1>s2, T1.team, T2.team) as t1,
		IF(s1>s2, S.s1, S.s2) as s1,
		IF(s1>s2, S.s2, S.s1) as s2,
		IF(s1>s2, T2.team, T1.team) as t2
		FROM $dbt_final_score S
		INNER JOIN $dbt_final F ON S.id_match = F.id
		INNER JOIN $dbt_games G ON F.id_game = G.id
		INNER JOIN $dbt_teams T1 ON F.id_t1 = T1.id
		INNER JOIN $dbt_teams T2 ON F.id_t2 = T2.id
		WHERE G.type = 'queen' AND (S.s1 > 19 OR S.s2 > 19)
		) as T
ORDER BY s1 DESC, s2 DESC");
    echo "<br><h3>QUEEN</h3>";
    echo "<table>";
        $i=1;foreach ($statistics as $value){
        echo "<tr>";
            echo "
            <td align='center'>".$i++."</td>
            <td>".$value['date']."</td>
            <td align='center'>".$value['game']."</td>
            <td align='center'>".$value['t1']."</td>
            <td align='center'>".$value['s1']."</td>
            <td align='center'>".$value['s2']."</td>
            <td align='center'>".$value['t2']."</td>
            ";
        echo "</tr>";
        }
    echo "</table>";
}
echo "</div>";