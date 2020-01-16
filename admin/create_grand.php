<?php

session_start();
require_once '../conf/dbconfig.php';
require_once '../func/func.php';
require_once '../func/header.php';
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if (null === $login) {
    die('Бородатый не годуе!');
}

$id_game = (int) $_GET['id'];
/*
echo "<center><a href='?id={$id_game}&type=8' class='type'>8</a> ";
echo "<a href='?id={$id_game}&type=16' class='type'>16</a> ";
echo "<a href='?id={$id_game}&type=32' class='type'>32</a></center><br>";*/

echo "<table class='grand'>
    <tr>
        <td class='grand'>";
        $j = 1;

            for ($i = 1; $i <= $_GET['type'] / 4; ++$i) {
                echo "<div class='final -grandlr -clear'>{$j}</div>
                    <div class='final -grandrr'>";
                if (null === $_SESSION['grand'][$j]) {
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>Команда</a>";
                } else {
                    $id_team = $_SESSION['grand'][$j];
                    $team = $conn->query("SELECT team FROM {$dbt_teams} WHERE id = {$id_team}")->fetch_assoc();
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>".$team['team'].'</a>';
                }
                echo '</div>';
                ++$j;
                echo "<div class='final -grandlr -even1 -clear'>{$j}</div>
                    <div class='final -grandrr'>";
                if (null === $_SESSION['grand'][$j]) {
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>Команда</a>";
                } else {
                    $id_team = $_SESSION['grand'][$j];
                    $team = $conn->query("SELECT team FROM {$dbt_teams} WHERE id = {$id_team}")->fetch_assoc();
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>".$team['team'].'</a>';
                }
                echo '</div>';
                ++$j;
            }
        echo "</td>
        <td class='grand' width='400px'>
        <center><img width='300px'src='../img/bpm.svg'></center>";

        echo "<form action='../func/create_grand.php' method='POST'>
                <input type='hidden' value='{$id_game}' name='id_game'>
                <input class='submit' type='submit' value='ПОГНАЛИ'>
            </form>";

        echo "</td>
        <td class='grand'>";
            for ($i = 1; $i <= $_GET['type'] / 4; ++$i) {
                echo "<div class='final -grandrl -clear'>";
                if (null === $_SESSION['grand'][$j]) {
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>Команда</a>";
                } else {
                    $id_team = $_SESSION['grand'][$j];
                    $team = $conn->query("SELECT team FROM {$dbt_teams} WHERE id = {$id_team}")->fetch_assoc();
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>".$team['team'].'</a>';
                }
                echo "</div>
                <div class='final -grandll '>{$j}</div>";
                ++$j;
                echo "<div class='final -grandrl -clear'>";
                if (null === $_SESSION['grand'][$j]) {
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>Команда</a>";
                } else {
                    $id_team = $_SESSION['grand'][$j];
                    $team = $conn->query("SELECT team FROM {$dbt_teams} WHERE id = {$id_team}")->fetch_assoc();
                    echo "<a href='?id=".$id_game.'&type='.$_GET['type'].'&pos='.$j."#grandCreate'>".$team['team'].'</a>';
                }
                echo "</div>
                <div class='final -grandll -even1 '>{$j}</div>";
                ++$j;
            }
        echo '</td>
    </tr>
</table>';

// ---------------Модальное окно------------------

echo "<div id='grandCreate' class='modalGrand'>
<div><a href='http://".$_SERVER['HTTP_HOST']."/admin/create_grand.php?id={$id_game}&type={$_GET['type']}#close' title='Закрыть' class='close'>x</a>
    <h3>Результаты матча</h3>
        Команда:
        <input type='text' name='team' placeholder='Название команды' class='grand input-team' autocomplete='off'>
        <input type='hidden' id='game' value='".$id_game."'>
        <input type='hidden' id='type' value='".$_GET['type']."'>
        <input type='hidden' id='pos' value='".$_GET['pos']."'>
        <div class='search_grand'></div>
</div>
    
</div>";
