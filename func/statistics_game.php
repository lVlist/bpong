<?php
require('../conf/dbconfig.php');
$id_game = $_POST['id'];

/* Получаем id команд квалификации и количество забитых и пропущеных  */
$qualification_hit_got = $conn->query("SELECT id_team, SUM(hit) as hit_cups,SUM(got) as got_cups 
FROM(
	SELECT id_t1 as id_team, SUM(s1) as hit, SUM(s2) as got
	FROM $dbt_q_games WHERE id_game = $id_game GROUP BY id_t1
	UNION ALL
	SELECT id_t2 as id_team, SUM(s2) as hit, SUM(s1) as got
	FROM $dbt_q_games WHERE id_game = $id_game GROUP BY id_t2
) as s
GROUP BY id_team");

/* Получаем id команд финала и количество забитых и пропущеных  */
$final_hit_got = $conn->query("SELECT id_team, SUM(hit) as hit_cups,SUM(got) as got_cups 
FROM(
	SELECT id_t1 as id_team, SUM(s1) as hit, SUM(s2) as got
	FROM $dbt_final WHERE id_game = $id_game GROUP BY id_t1
	UNION ALL
	SELECT id_t2 as id_team, SUM(s2) as hit, SUM(s1) as got
	FROM $dbt_final WHERE id_game = $id_game GROUP BY id_t2
) as s
GROUP BY id_team");

$stmt = $conn->prepare("UPDATE `$dbt_statistics` SET  `hit_cups`=?, `got_cups`=?, `difference_cups`=? WHERE (`id_game`=?) AND (`id_team`=?)"); 
$stmt->bind_param('iiiii', $hit_cups, $got_cups, $difference_cups, $id_game, $id_team);

foreach ($qualification_hit_got as $value){
	$id_team = $value['id_team'];
	foreach ($final_hit_got as $final){
		if($value['id_team'] == $final['id_team']){
			$hit_cups = $value['hit_cups']+$final['hit_cups'];
			$got_cups = $value['got_cups']+$final['got_cups'];
			break;
		}else{
			$hit_cups = $value['hit_cups'];
			$got_cups = $value['got_cups'];
		}
	}
	$difference_cups = $hit_cups-$got_cups;
	$stmt->execute();
}

/* Получаем id команд квалификации и их количество побед и пройгрышей */
$qualification_games = $conn->query("SELECT id_team, 
SUM(wins) as wins, 
SUM(losses) as losses, 
SUM(wins_ot) as wins_ot, 
SUM(losses_ot) as losses_ot
FROM(
		SELECT id_team,
		IF (r1=3, 1,0)+IF (r2=3, 1,0)+IF (r3=3, 1,0) as wins,
		IF (r1=0, 1,0)+IF (r2=0, 1,0)+IF (r3=0, 1,0) as losses,
		IF (r1=2, 1,0)+IF (r2=2, 1,0)+IF (r3=2, 1,0) as wins_ot,
		IF (r1=1, 1,0)+IF (r2=1, 1,0)+IF (r3=1, 1,0) as losses_ot
		FROM $dbt_qualification WHERE id_game = $id_game
	UNION ALL
		SELECT id_team, 
		SUM(wins) as wins, 
		SUM(losses) as losses, 
		SUM(wins_over) as wins_ot, 
		SUM(losses_over) as losses_ot
		FROM $dbt_statistics_final WHERE id_game = $id_game GROUP BY id_team
) as s
GROUP BY id_team");

$stmt = $conn->prepare("UPDATE `$dbt_statistics` SET `wins`=?, `losses`=?, `wins_over`=?, `losses_over`=? WHERE (`id_game`=?) AND (`id_team`=?)"); 
$stmt->bind_param('iiiiii', $wins, $losses, $wins_over, $losses_over, $id_game, $id_team);

foreach ($qualification_games as $value){

	$id_team = $value['id_team'];

	// если 0 то NULL
	if ($value['wins'] == 0){$wins = NULL;}else{$wins = $value['wins'];}
	if ($value['losses'] == 0){$losses = NULL;}else{$losses = $value['losses'];}
	if ($value['wins_ot'] == 0){$wins_over = NULL;}else{$wins_over = $value['wins_ot'];}
	if ($value['losses_ot'] == 0){$losses_over = NULL;}else{$losses_over = $value['losses_ot'];}
	$stmt->execute();
}


/* Очки за место */
$type = $conn->query("SELECT type FROM $dbt_games Q WHERE Q.id = $id_game")->fetch_assoc();
$array_score = $conn->query("SELECT * FROM $dbt_score ORDER BY sat DESC");
foreach($array_score as $value){
    $array_points[] = $value[$type['type']];
}

$i=0;

$stmt = $conn->prepare("UPDATE `$dbt_statistics` SET `points`=? WHERE (`id_game`=?) AND (`id_team`=?)"); 
$stmt->bind_param('iii', $points, $id_game, $id_team);

/* 1 место */
$final_id_teams = $conn->query("SELECT id_t1, s1, s2, id_t2, block FROM $dbt_final 
WHERE id_game = $id_game AND round = (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1)");

//определяю кто выйграл 2 игры
$t1 = 0; $t2 = 0;
foreach($final_id_teams as $value){
	if($value['s1']>$value['s2']){
		$t1 += 1;
	}else{
		$t2 += 1;
	}
}

//определяю и записываю 1,2 место
if ($t1 > $t2){
	$id_team = $value['id_t1'];
	$points = $array_points[$i++];
	$stmt->execute();
	$id_team = $value['id_t2'];
	$points = $array_points[$i++];
	$stmt->execute();
}else{
	$id_team = $value['id_t2'];
	$points = $array_points[$i++];
	$stmt->execute();
	$id_team = $value['id_t1'];
	$points = $array_points[$i++];
	$stmt->execute();
}

/* 3 место */
$final_id_teams = $conn->query("SELECT IF ((s1 > s2),id_t1,id_t2) AS id_team FROM $dbt_final
WHERE id_game = $id_game AND round = (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1) - 1");
$final_id_team = $final_id_teams->fetch_assoc();

//записываем
$id_team = $final_id_team['id_team'];
$points = $array_points[$i++];
$stmt->execute();

/* Финал кроме 3,2,1 места (делаю выборку по командам которые проиграли) */
$final_id_teams = $conn->query("SELECT IF ((s1 > s2),id_t2,id_t1) AS id_team FROM $dbt_final
WHERE id_game = $id_game AND
round != (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1) AND
round != (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1)-2
ORDER BY round DESC");

foreach($final_id_teams as $value){
	$id_team = $value['id_team'];
	$points = $array_points[$i++];
	$stmt->execute();
}

/* Команды квалификации (делаю выборку команд которые не прошли в финал) */
$qualification_id_teams = $conn->query("SELECT id_team FROM $dbt_qualification
WHERE id_game = $id_game AND id_team NOT IN (
	SELECT team
	FROM(
		SELECT id_t1 as team FROM $dbt_final WHERE id_game = $id_game
		UNION ALL
		SELECT id_t2 as team FROM $dbt_final WHERE id_game = $id_game) as f
	GROUP BY team)
ORDER BY result DESC, difference DESC");

foreach($qualification_id_teams as $value){
	$id_team = $value['id_team'];
	$points = $array_points[$i++];
	$stmt->execute();
}

header('Location: ../admin/statistics.php?id='.$id_game);