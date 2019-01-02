<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if ($login != null)
{
    if($_GET){
        $id_game = (int)$_GET['id_game'];
    }else{
        die;
    }

$edit_view = $conn->query("SELECT games.id AS id_game, games.game, teams.id AS id_team, teams.team FROM qualification
INNER JOIN games ON games.id = qualification.id_game
INNER JOIN teams ON teams.id = qualification.id_team
WHERE qualification.id_game = $id_game");
$game = $edit_view->fetch_assoc();


echo "<div id='create'>
<form action='http://".$_SERVER['HTTP_HOST']."/func/q_game.php' method='POST'>
    Название турнира: <input class='input-block' type='text' name='game' value='".$game['game']."'> Команды:";
    $team = '';
    $id_team = '';
    foreach($edit_view as $value){
        $id_team .= $value['id_team'].",";
        $team .= $value['team']."\n";
    }
$team = substr($team, 0, -2);
$id_team =substr($id_team, 0, -1);
echo "<textarea class='input-block' name='teams' rows='20' cols='30'>".$team."</textarea>";
echo "<input type='hidden' name='id_game'  value='".$game['id_game']."'>";
echo "<input type='hidden' name='id_teams'  value='".$id_team."'>";
echo "<input class ='submit' type='submit' name='delete' value='СОХРАНИТЬ'>";
echo "</form>";
echo "</div>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";