<?php

require '../conf/dbconfig.php';

$id_match = (int) $_POST['id_match'];
$id_game = (int) $_POST['id_game'];
$id_t1 = (int) $_POST['id_t1'];
$id_t2 = (int) $_POST['id_t2'];
$block = (int) $_POST['block'];
$next_block = (int) $_POST['next_block'];
$next_block_position = (int) $_POST['next_block_position'];
$round = (int) $_POST['round'];
$next_round = $round + 1;
$position = $_POST['position'];


// Получаем id последнего раунда
$last_round = $conn->query("SELECT F.round FROM {$dbt_grand} F WHERE F.id_game = {$id_game} AND F.position = '{$position}' ORDER BY F.round DESC LIMIT 1")->fetch_assoc();

if (isset($_POST)) {
    // Записываем счет за игру
    $stmt = $conn->prepare("INSERT INTO {$dbt_grand_score} (id_match, id_game, s1, s2) VALUES (?,?,?,?)");
    $stmt->bind_param('iiii', $id_match, $id_game, $s1, $s2);

    if ($_POST['us1'] && $_POST['us2']) {
        $conn->query("DELETE FROM `{$dbt_grand_score}` WHERE (`id_game` = {$id_game}) AND (`id_match` = {$id_match})");
        $i = 0;
        foreach ($_POST['us1'] as $value) {
            $s1 = $_POST['us1'][$i];
            $s2 = $_POST['us2'][$i];
            $stmt->execute();
            ++$i;
        }
    }

    if ($_POST['is1'] && $_POST['is2']) {
        $i = 0;
        foreach ($_POST['is1'] as $value) {
            $s1 = $_POST['is1'][$i];
            $s2 = $_POST['is2'][$i];
            $stmt->execute();
            ++$i;
        }
    }

    // Оперделяем кто победил
    $final_score = $conn->query("SELECT id, s1, s2 FROM {$dbt_grand_score} WHERE id_match = {$id_match} AND id_game = {$id_game}");

    $s1 = 0;
    $s2 = 0;
    foreach ($final_score as $gs) {
        if ($gs['s1'] > $gs['s2']) {
            ++$s1;
        } else {
            ++$s2;
        }
    }

    // Игры верхней сетки
    //определяем подедителя верхней сетки
    if ('up' == $position) {
        if ($s1 > $s2) {
            $id_team = $id_t1;
        } else {
            $id_team = $id_t2;
        }

        if ($round != (int) $last_round['round']) {
            $stmt = $conn->prepare("UPDATE `{$dbt_grand}` SET `id_t{$next_block_position}`=? WHERE (`id_game`=?) AND (`round`=?) AND (`block`=?) AND (`position`=?)");

            if ($s1 == $s2) {
                $team_null = 1;
                $stmt->bind_param('iiiis', $team_null, $id_game, $next_round, $next_block, $position);
                $stmt->execute();
            } else {
                $stmt->bind_param('iiiis', $id_team, $id_game, $next_round, $next_block, $position);
                $stmt->execute();
            }
        }

        //проигравшею команду записываем в нижнею сетку
        if ($s1 < $s2) {
            $id_team = $id_t1;
        } else {
            $id_team = $id_t2;
        }

        if ($round != (int) $last_round['round']) {
            $position = 'down';

        $game24 = $conn->query("SELECT IF (COUNT(position) = 8, 1, 0) as count
                                        FROM bpm_grand WHERE position = 'down' AND (round = 2 OR round = 3) AND id_game = {$id_game}")->fetch_assoc();

        switch ($round) {
            case 2:
                (int)$game24['count'] === 1 ? $round = 1 : $round = 2;
                $next_block_position = 1;
                $array_block = [
                    '1' => '8',
                    '2' => '7',
                    '3' => '6',
                    '4' => '5',
                    '5' => '4',
                    '6' => '3',
                    '7' => '2',
                    '8' => '1',
                ];
                $block = $array_block[$block];

                break;
            case 3:
                (int)$game24['count'] === 1 ? $round = 3 : $round = 4;
                $next_block_position = 1;
                $array_block = [
                    '1' => '2',
                    '2' => '1',
                    '3' => '4',
                    '4' => '3',
                ];
                $block = $array_block[$block];

                break;
            case 4:
                (int)$game24['count'] === 1 ? $round = 5 : $round = 6;
                $next_block_position = 1;
                $array_block = [
                    '1' => '2',
                    '2' => '1',
                ];
                $block = $array_block[$block];

                break;
            case 5:
                (int)$game24['count'] === 1 ? $round = 7 : $round = 8;
                $next_block_position = 1;

                break;
            default:
                $block = $next_block;
        }

            $stmt = $conn->prepare("UPDATE `{$dbt_grand}` SET `id_t{$next_block_position}`=? WHERE (`id_game`=?) AND (`round`=?) AND (`block`=?) AND (`position`=?)");

            if ($s1 == $s2) {
                $stmt->bind_param('iiiis', $team_null, $id_game, $round, $block, $position);
                $stmt->execute();
            } else {
                $stmt->bind_param('iiiis', $id_team, $id_game, $round, $block, $position);
                $stmt->execute();
            }
        }
        $position = 'up'; //для якоря
    } elseif ('down' == $position) { //определяем победителя нижней сетки
        if ($s1 > $s2) {
            $id_team = $id_t1;
        } else {
            $id_team = $id_t2;
        }



        $stmt = $conn->prepare("UPDATE `{$dbt_grand}` SET `id_t{$next_block_position}`=? WHERE (`id_game`=?) AND (`round`=?) AND (`block`=?) AND (`position`=?)");

        if ($round != (int) $last_round['round']) {
            if ($s1 == $s2) {
                $team_null = 1;
                $stmt->bind_param('iiiis', $team_null, $id_game, $next_round, $next_block, $position);
                $stmt->execute();
            } else {
                $stmt->bind_param('iiiis', $id_team, $id_game, $next_round, $next_block, $position);
                $stmt->execute();
            }
        } elseif ($round === (int) $last_round['round']) {//записываем победителя нижней сетки в финал верхней сетки
            $position = 'up';
            $round = $conn->query("SELECT round FROM {$dbt_grand} WHERE id_game = {$id_game} AND position = 'up' ORDER BY round DESC LIMIT 1")->fetch_assoc();
            $round = $round['round'];
            //var_dump($position);die;
            $stmt->bind_param('iiiis', $id_team, $id_game, $round, $next_block, $position);
            $stmt->execute();
        }
    }
    header('Location: ../admin/grand.php?id='.$id_game.'#'.$position);
    exit;
}
