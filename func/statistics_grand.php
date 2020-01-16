<?php

require '../conf/dbconfig.php';
$id_game = (int) $_POST['id'];

// Получаем id команд финала и количество забитых и пропущеных
$grand_hit_got = $conn->query("SELECT id_team, SUM(hit) as hit_cups,SUM(got) as got_cups 
FROM(
		SELECT id_t1 as id_team, SUM(s1) as hit, SUM(s2) as got
		FROM {$dbt_grand} G
		INNER JOIN {$dbt_grand_score} S ON S.id_match = G.id
		WHERE G.id_game = {$id_game} GROUP BY id_t1
	UNION ALL
		SELECT id_t2 as id_team, SUM(s2) as hit, SUM(s1) as got
		FROM {$dbt_grand} G
		INNER JOIN {$dbt_grand_score} S ON S.id_match = G.id
		WHERE G.id_game = {$id_game} GROUP BY id_t2
) as s
GROUP BY id_team");

$stmtSc = $conn->prepare("UPDATE `{$dbt_statistics}` SET  `hit_cups`=?, `got_cups`=?, `difference_cups`=? WHERE (`id_game`=?) AND (`id_team`=?)");
$stmtSc->bind_param('iiiii', $hit_cups, $got_cups, $difference_cups, $id_game, $id_team);

foreach ($grand_hit_got as $grand) {
    $id_team = (int) $grand['id_team'];
    $hit_cups = (int) $grand['hit_cups'];
    $got_cups = (int) $grand['got_cups'];
    $difference_cups = $hit_cups - $got_cups;
    $stmtSc->execute();
}

// Получаем id команд квалификации и финала и их количество побед и пройгрышей
$qualification_games = $conn->query("SELECT id_team,
	SUM(wins) as wins,
	SUM(losses) as losses,
	SUM(wins_ot) as wins_ot,
	SUM(losses_ot) as losses_ot
FROM(
		SELECT id_t1 as id_team,
		SUM(IF(s1>s2,IF(s2>=10,0,1),0))as wins,
		SUM(IF(s2>s1,IF(s1>=10,0,1),0))as losses,
		SUM(IF(s1>s2,IF(s2>=10,1,0),0)) as wins_ot,		
		SUM(IF(s2>s1,IF(s1>=10,1,0),0)) as losses_ot
		FROM {$dbt_grand} f
		INNER JOIN {$dbt_grand_score} s ON s.id_match = f.id
		WHERE f.id_game = {$id_game} GROUP BY f.id_t1
	UNION ALL
		SELECT id_t2 as id_team,
		SUM(IF(s2>s1,IF(s1>=10,0,1),0))as wins,
		SUM(IF(s1>s2,IF(s2>=10,0,1),0))as losses,
		SUM(IF(s2>s1,IF(s1>=10,1,0),0)) as wins_ot,		
		SUM(IF(s1>s2,IF(s2>=10,1,0),0)) as losses_ot
		FROM {$dbt_grand} f
		INNER JOIN {$dbt_grand_score} s ON s.id_match = f.id
		WHERE f.id_game = {$id_game} GROUP BY f.id_t2
) as s
GROUP BY id_team");

$stmt = $conn->prepare("UPDATE `{$dbt_statistics}` SET `wins`=?, `losses`=?, `wins_over`=?, `losses_over`=? WHERE (`id_game`=?) AND (`id_team`=?)");
$stmt->bind_param('iiiiii', $wins, $losses, $wins_over, $losses_over, $id_game, $id_team);

foreach ($qualification_games as $value) {
    $id_team = $value['id_team'];

    // если 0 то NULL
    if (0 == $value['wins']) {
        $wins = null;
    } else {
        $wins = $value['wins'];
    }
    if (0 == $value['losses']) {
        $losses = null;
    } else {
        $losses = $value['losses'];
    }
    if (0 == $value['wins_ot']) {
        $wins_over = null;
    } else {
        $wins_over = $value['wins_ot'];
    }
    if (0 == $value['losses_ot']) {
        $losses_over = null;
    } else {
        $losses_over = $value['losses_ot'];
    }
    $stmt->execute();
}

// Очки за место

//Записываем очки за места в массив в зависимости от типа турнира
$type = $conn->query("SELECT type FROM {$dbt_games} Q WHERE Q.id = {$id_game}")->fetch_assoc();

$array_score = $conn->query("SELECT * FROM {$dbt_score} ORDER BY sat DESC");
foreach ($array_score as $value) {
    $array_points[] = $value['sat'];
}

//определяем победителей финала

$sql = "SELECT
IF( Count(IF(s1>s2,id_t1,NULL)) > Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) AS m1,
IF( Count(IF(s1>s2,id_t1,NULL)) < Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) AS m2
FROM {$dbt_grand} g INNER JOIN {$dbt_grand_score} s ON s.id_match = g.id
WHERE g.id_game = {$id_game} AND g.position = 'up' AND g.round = (SELECT round FROM {$dbt_grand} WHERE id_game = {$id_game} AND position = 'up' ORDER BY round DESC LIMIT 1)
GROUP BY id_t1, id_t2";

$final_winners = $conn->query($sql)->fetch_assoc();

$stmt = $conn->prepare("UPDATE `{$dbt_statistics}` SET `points`= ?, `final`= ? WHERE (`id_game`=?) AND (`id_team`=?)");
$stmt->bind_param('iiii', $points, $final_bool, $id_game, $id_team);

$final_bool = 1; //помечаем играла команда в финале или нет  1-да 0-нет
$i = 0;
foreach ($final_winners as $value) {
    $id_team = $value;
    $points = $array_points[$i++];
    $stmt->execute();
}

// Финал кроме 2,1 места (делаю выборку по командам которые проиграли) */
$final_id_teams = $conn->query("SELECT
IF( Count(IF(s1>s2,id_t1,NULL)) < Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) AS id_team
FROM {$dbt_grand} AS g  INNER JOIN {$dbt_grand_score} AS s ON s.id_match = g.id
WHERE g.id_game = {$id_game} AND g.position = 'down'
GROUP BY id_t1, id_t2, round
ORDER BY round DESC");

foreach ($final_id_teams as $value) {
    $id_team = $value['id_team'];
    $points = $array_points[$i++];
    $stmt->execute();
}

header('Location: ../admin/statistics.php?id='.$id_game);
exit;
