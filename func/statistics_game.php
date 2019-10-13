<?php
require('../conf/dbconfig.php');
$id_game = $_POST['id'];

// получаем статус о игре за 3-е место
$bronze = $conn->query("SELECT bronze FROM $dbt_games WHERE id = $id_game")->fetch_assoc();

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
		FROM $dbt_final
		INNER JOIN $dbt_final_score ON $dbt_final_score.id_match = $dbt_final.id
		WHERE $dbt_final.id_game = $id_game GROUP BY id_t1
	UNION ALL
		SELECT id_t2 as id_team, SUM(s2) as hit, SUM(s1) as got
		FROM $dbt_final
		INNER JOIN $dbt_final_score ON $dbt_final_score.id_match = $dbt_final.id
		WHERE $dbt_final.id_game = $id_game GROUP BY id_t2
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

/* Получаем id команд квалификации и финала и их количество побед и пройгрышей */
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
		SELECT id_t1 as id_team,
		SUM(IF(s1>s2,IF(s2>=10,0,1),0))as wins,
		SUM(IF(s2>s1,IF(s1>=10,0,1),0))as losses,
		SUM(IF(s1>s2,IF(s2>=10,1,0),0)) as wins_ot,		
		SUM(IF(s2>s1,IF(s1>=10,1,0),0)) as losses_ot
		FROM $dbt_final f
		INNER JOIN $dbt_final_score s ON s.id_match = f.id
		WHERE f.id_game = $id_game GROUP BY f.id_t1
	UNION ALL
		SELECT id_t2 as id_team,
		SUM(IF(s2>s1,IF(s1>=10,0,1),0))as wins,
		SUM(IF(s1>s2,IF(s2>=10,0,1),0))as losses,
		SUM(IF(s2>s1,IF(s1>=10,1,0),0)) as wins_ot,		
		SUM(IF(s1>s2,IF(s2>=10,1,0),0)) as losses_ot
		FROM $dbt_final f
		INNER JOIN $dbt_final_score s ON s.id_match = f.id
		WHERE f.id_game = $id_game GROUP BY f.id_t2
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

//Записываем очки за места в массив в зависимости от типа турнира
$type = $conn->query("SELECT type FROM $dbt_games Q WHERE Q.id = $id_game")->fetch_assoc();

$array_score = $conn->query("SELECT * FROM $dbt_score ORDER BY sat DESC");
foreach($array_score as $value)
{
    $array_points[] = $value[$type['type']];
}

//определяем победителей финала
if((int)$bronze['bronze'] === 1) //если есть игра за 3-е место
{//определяем 1,2,3,4 место 
	$sql = "SELECT MAX(m1) m1, MAX(m2) m2, MAX(m3) m3, MAX(m4) m4 FROM 
	(
		SELECT
		f.id_game,
		IF( Count(IF(s1>s2,id_t1,NULL)) > Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) AS m1,
		IF( Count(IF(s1>s2,id_t1,NULL)) < Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) AS m2,
		0 AS m3,
		0 AS m4
		FROM $dbt_final f INNER JOIN $dbt_final_score s ON s.id_match = f.id
		WHERE f.id_game = $id_game AND f.round = (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1,1)
		GROUP BY id_t1, id_t2
	UNION ALL
		SELECT
		f.id_game,
		0 as m1, 0 as m2,
		IF( Count(IF(s1>s2,id_t1,NULL)) > Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) as m3,
		IF( Count(IF(s1>s2,id_t1,NULL)) < Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) as m4
		FROM $dbt_final f INNER JOIN $dbt_final_score s ON s.id_match = f.id
		WHERE f.id_game = $id_game AND f.round = (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1)
		GROUP BY id_t1, id_t2
	) s
	GROUP BY id_game";
}
else //если нету игры за 3-е место
{
	$sql = "SELECT
	IF( Count(IF(s1>s2,id_t1,NULL)) > Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) as m1,
	IF( Count(IF(s1>s2,id_t1,NULL)) < Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) as m2
	FROM $dbt_final f INNER JOIN $dbt_final_score s ON s.id_match = f.id
	WHERE f.id_game = $id_game AND f.round = (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1)
	GROUP BY id_t1, id_t2";
}

$final_winners = $conn->query($sql)->fetch_assoc();

$stmt = $conn->prepare("UPDATE `$dbt_statistics` SET `points`= ?, `final`= ? WHERE (`id_game`=?) AND (`id_team`=?)"); 
$stmt->bind_param('iiii', $points, $final_bool, $id_game, $id_team);

$final_bool = 1;//помечаем играла команда в финале или нет  1-да 0-нет
$i=0;
foreach($final_winners as $value){
	$id_team = $value;
	$points = $array_points[$i++];
	$stmt->execute();
}


if((int)$bronze['bronze'] === 1) //если есть игра за 3-е место
{ 
	// Финал кроме 4,3,2,1 места (делаю выборку по командам которые проиграли) */
	$final_id_teams = $conn->query("SELECT
	IF( Count(IF(s1>s2,id_t1,NULL)) < Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) AS id_team
	FROM $dbt_final AS f  INNER JOIN $dbt_final_score AS s ON s.id_match = f.id
	WHERE f.id_game = $id_game AND
	round != (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1) AND
	round != (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1, 1) AND
	round != (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 2, 1)
	GROUP BY id_t1, id_t2, round
	ORDER BY round DESC");

	foreach($final_id_teams as $value){
		$id_team = $value['id_team'];
		$points = $array_points[$i++];
		$stmt->execute();
	}
}
else
{
	// Финал кроме 2,1 места (делаю выборку по командам которые проиграли) */
	$final_id_teams = $conn->query("SELECT
	IF( Count(IF(s1>s2,id_t1,NULL)) < Count(IF(s1<s2,id_t2,NULL)), id_t1 , id_t2 ) AS id_team
	FROM $dbt_final AS f  INNER JOIN $dbt_final_score AS s ON s.id_match = f.id
	WHERE f.id_game = $id_game AND 
	round != (SELECT round FROM $dbt_final WHERE id_game = $id_game ORDER BY round DESC LIMIT 1)
	GROUP BY id_t1, id_t2, round
	ORDER BY round DESC");

	foreach($final_id_teams as $value){
		$id_team = $value['id_team'];
		$points = $array_points[$i++];
		$stmt->execute();
	}
}

$final_bool = 0; //почечаем играла команда в финале или нет 0-нет 1-да
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
exit;