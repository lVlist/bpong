<?php

require_once '../conf/dbconfig.php';
require_once '../func/func.php';
require_once '../func/header.php';
menu();
menuAdmin();

$login = getUserLogin();
if (null === $login) {
    die('Будник не одобряет');
}

// получаем id игры методом GET если нету последний id из БД
$id_last_game = $conn->query("SELECT G.id FROM {$dbt_games} G ORDER BY G.id DESC LIMIT 1");
$last_game = $id_last_game->fetch_assoc();

// получаем id игры
if (isset($_GET['id'])) {
    $id_game = (int) $_GET['id'];
} else {
    $id_game = (int) $last_game['id'];
}

// выборка финала
$final = $conn->query("SELECT F.id, t1.team t1, t2.team t2, F.position, F.round, F.block, F.next_block, F.next_block_position, F.id_t1, F.id_t2 
FROM {$dbt_grand} F
INNER JOIN {$dbt_teams} t1 ON t1.id = IFNULL(F.id_t1, 1)
INNER JOIN {$dbt_teams} t2 ON t2.id = IFNULL(F.id_t2, 1)
WHERE F.id_game = {$id_game} AND F.position = 'up' ORDER BY id ASC");

// получаем последний раунд
$last_round = $conn->query("SELECT round, block FROM {$dbt_grand} WHERE id_game = {$id_game} AND position = 'up' ORDER BY round DESC LIMIT 1")->fetch_assoc();

// количество команд
$quantity_team_game = $conn->query("SELECT Count(id_t1) + Count(id_t2) as quantity FROM {$dbt_grand} WHERE round = 1 AND id_game = {$id_game} AND position = 'up'")->fetch_assoc();

echo "<div id='grand'>";
echo "<div id='grandUp{$quantity_team_game['quantity']}'>";
echo "<a name='up'></a>";
    echo "<table style='margin:5px;'><tr>";

    for ($i = 1; $i <= $last_round['round']; ++$i) {
        echo "<td style='width: 173px; text-align: center'>";

        if ($i === (int) $last_round['round']) {
            echo 'Финал';
        } else {
            echo "Раунд {$i}";
        }

        echo '</td>';
    }
echo '</tr></table>';

for ($i = 1; $i <= $last_round['round']; ++$i) {
    // Cчетчик стиля id=even
    $e = $i;

    // Cчетчик стиля id=even для игр 12 и 24
    if (12 == $final->num_rows or 24 == $final->num_rows) {
        if ($i >= 2) {
            $e = $i - 1;
        }
    }

    if ($i == $last_round['round']) {
        echo "<div id='final".$final->num_rows."'>";
        echo "<div style='margin: 5px 0px'>Финал:</div>";
    } else {
        echo "<div id='even".$e."'>";
    }

    // Выводим команды и счет
    foreach ($final as $value) {
        $match_id = $value['id'];
        if ($value['round'] == $i) {
            // Оперделяем кто победил
            $grand_score = $conn->query("SELECT id, s1, s2 FROM {$dbt_grand_score} WHERE id_match = {$match_id} AND id_game = {$id_game}");
            $t1 = null;
            $t2 = null;
            foreach ($grand_score as $gs) {
                if ($gs['s1'] > $gs['s2']) {
                    ++$t1;
                } else {
                    ++$t2;
                }
            }

            $url = "<a href='?id=".$id_game.'&id_match='.$value['id'].'&position='.$value['position'].'&round='.$value['round'].'&block='.$value['block'].'&next_block='.$value['next_block'].'&next_block_position='.$value['next_block_position'].'&id_t1='.$value['id_t1'].'&id_t2='.$value['id_t2'].'&t1='.$value['t1'].'&t2='.$value['t2']."#grandEdit'>";
            $img_edit = "<img width='13px' src='http://".$_SERVER['HTTP_HOST']."/img/edit_score.png'>";

            // Команда 1
            echo "<div class='final -clear'>".$value['t1'].'</div>';

            if ($t1 > $t2) {
                echo "<div class='final -score -color'>";
            } else {
                echo "<div class='final -score'>";
            }

            if (null === $t1 && null === $t2) {
                echo $url.$img_edit.'</a></div>';
            } else {
                if (($t1 <= 1 && 0 == $t2) || (0 == $t1 && $t2 <= 1)) {
                    echo $url.$gs['s1'].'</a></div>';
                } else {
                    echo $url.(int) $t1.'</a></div>';
                }
            }

            // Команда 2
            if ($i == $last_round['round'] || $last_round['round'] - 1 == $i) {
                echo "<div class='final -final -clear'>".$value['t2'].'</div>';
            } else {
                echo "<div class='final -even".$e." -clear'>".$value['t2'].'</div>';
            }

            if ($t2 > $t1) {
                echo "<div class='final -score -color'>";
            } else {
                echo "<div class='final -score'>";
            }

            if (null === $t1 && null === $t2) {
                echo $url.$img_edit.'</a></div>';
            } else {
                if (($t1 <= 1 && 0 == $t2) || (0 == $t1 && $t2 <= 1)) {
                    echo $url.$gs['s2'].'</a></div>';
                } else {
                    echo $url.(int) $t2.'</a></div>';
                }
            }
        }
    }

    if ($i == $last_round['round']) {
        echo "<form action='../func/statistics_grand.php' method='POST'>
            <input type='hidden' name='id' value='".$id_game."'>
            <input class ='submit -addteam' type='submit' value='Завершить турнир'>
        </form>";
    }

    echo '</div>';
}
echo '</div>';

// выборка финала
$final = $conn->query("SELECT F.id, t1.team t1, t2.team t2, F.position, F.round, F.block, F.next_block, F.next_block_position, F.id_t1, F.id_t2 
FROM {$dbt_grand} F
INNER JOIN {$dbt_teams} t1 ON t1.id = IFNULL(F.id_t1, 1)
INNER JOIN {$dbt_teams} t2 ON t2.id = IFNULL(F.id_t2, 1)
WHERE F.id_game = {$id_game} AND F.position = 'down' ORDER BY id ASC");

// получаем последний раунд
$last_round = $conn->query("SELECT round, block FROM {$dbt_grand} WHERE id_game = {$id_game} AND position = 'down' ORDER BY round DESC LIMIT 1")->fetch_assoc();

// -----------------Нижняя сетка ------------------------
echo "<div id='grandDown'>";
    echo "<a name='down'></a>";
        echo "<table style='margin:5px;'><tr>";
            for ($i = 1; $i <= $last_round['round']; ++$i) {
                echo "<td style='width: 173px; text-align: center'> Раунд нижней сетки {$i} </td>";
            }
        echo '</tr></table>';

    for ($i = 1; $i <= $last_round['round']; ++$i) {
        // Cчетчик стиля id=even
        $e = $i;

        echo "<div id='geven".$e."'>";

        // Выводим команды и счет
        foreach ($final as $value) {
            $match_id = $value['id'];
            if ($value['round'] == $i) {
                // Оперделяем кто победил
                $grand_score = $conn->query("SELECT id, s1, s2 FROM {$dbt_grand_score} WHERE id_match = {$match_id} AND id_game = {$id_game}");
                $t1 = null;
                $t2 = null;
                foreach ($grand_score as $gs) {
                    if ($gs['s1'] > $gs['s2']) {
                        ++$t1;
                    } else {
                        ++$t2;
                    }
                }

                $url = "<a href='?id=".$id_game.'&id_match='.$value['id'].'&position='.$value['position'].'&round='.$value['round'].'&block='.$value['block'].'&next_block='.$value['next_block'].'&next_block_position='.$value['next_block_position'].'&id_t1='.$value['id_t1'].'&id_t2='.$value['id_t2'].'&t1='.$value['t1'].'&t2='.$value['t2']."#grandEdit'>";
                $img_edit = "<img width='13px' src='http://".$_SERVER['HTTP_HOST']."/img/edit_score.png'>";

                // Команда 1
                echo "<div class='final -clear'>".$value['t1'].'</div>';

                if ($t1 > $t2) {
                    echo "<div class='final -score -color'>";
                } else {
                    echo "<div class='final -score'>";
                }

                if (null === $t1 && null === $t2) {
                    echo $url.$img_edit.'</a></div>';
                } else {
                    if (($t1 <= 1 && 0 == $t2) || (0 == $t1 && $t2 <= 1)) {
                        echo $url.$gs['s1'].'</a></div>';
                    } else {
                        echo $url.(int) $t1.'</a></div>';
                    }
                }

                // Команда 2
                if ($i == $last_round['round']) {
                    echo "<div class='final -final -clear'>".$value['t2'].'</div>';
                } else {
                    echo "<div class='final -geven".$e." -clear'>".$value['t2'].'</div>';
                }

                if ($t2 > $t1) {
                    echo "<div class='final -score -color'>";
                } else {
                    echo "<div class='final -score'>";
                }

                if (null === $t1 && null === $t2) {
                    echo $url.$img_edit.'</a></div>';
                } else {
                    if (($t1 <= 1 && 0 == $t2) || (0 == $t1 && $t2 <= 1)) {
                        echo $url.$gs['s2'].'</a></div>';
                    } else {
                        echo $url.(int) $t2.'</a></div>';
                    }
                }
            }
        }
        echo '</div>';
    }
    echo '</div>';
echo '</div>';

    // ---------------Модальное окно------------------

    $id_match = $_GET['id_match'];

    $grand = $conn->query("SELECT id, s1, s2 FROM {$dbt_grand_score} WHERE id_match = {$id_match} AND id_game = {$id_game}");

    echo "<div id='grandEdit' class='modalGrand'>
    <div><a href='http://".$_SERVER['HTTP_HOST']."/admin/grand.php?id={$id_game}#close' title='Закрыть' class='close'>x</a>
        <h3>Результаты матча</h3>
        <form action='http://".$_SERVER['HTTP_HOST']."/func/grand_game.php' method='POST'>
            <input type='hidden' name='id_match' value='".$id_match."'>
            <input type='hidden' name='id_game'  value='".$id_game."'>
            <input type='hidden' name='position'  value='".$_GET['position']."'>
            <input type='hidden' name='round'  value='".$_GET['round']."'>
            <input type='hidden' name='block'  value='".$_GET['block']."'>
            <input type='hidden' name='next_block'  value='".$_GET['next_block']."'>
            <input type='hidden' name='next_block_position'  value='".$_GET['next_block_position']."'>
            <input type='hidden' name='id_t1'  value='".$_GET['id_t1']."'>
            <input type='hidden' name='id_t2'  value='".$_GET['id_t2']."'>

            <table>
            <tr>
                <td colspan='6' style='border: 0px; background: #2B303D';><div class='counter' onclick='addInput()'>добавить</div><div class='counter' onclick='delInput()'>удалить</div></td>
            </tr>
            <tr>
                <td>".$_GET['t1']."</td>
                <td>
                    <div id='profile'>
                        <div>";
                            $i = 0;
                            foreach ($grand as $val1) {
                                ++$i;
                                echo "<input type='number' autocomplete='off' class='form-grand' name='us1[]' value='{$val1['s1']}'>";
                            }

                            echo "</div>
                            <div id='input0'>";

                            if (0 == $grand->num_rows && $i <= 4) {
                                echo "<input type='number' autocomplete='off' class='form-grand' name='is1[]'>";
                            }
                        echo '</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>'.$_GET['t2']."</td>
                <td>
                    <div id='profile2'>
                        <div>";
                            foreach ($grand as $val2) {
                                echo "<input type='number' autocomplete='off' class='form-grand' name='us2[]' value='{$val2['s2']}'>";
                            }

                            echo "</div>
                            <div id='input0'>";

                            if (0 == $grand->num_rows && $i <= 4) {
                                echo "<input type='number' autocomplete='off' class='form-grand' name='is2[]'>";
                            }
                        echo '</div>
                    </div>       
                </td>
            </tr>';
    echo "</table>
        <input class ='submit' type='submit' value='Сохранить'></form>
    </div>

</div>";
