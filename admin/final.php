<?php
require_once '../conf/dbconfig.php';
require_once '../func/func.php';
require_once '../func/header.php';
menu();
menuAdmin();
$login = getUserLogin();

// получаем id игры методом GET если нету последний id из БД
$id_last_game = $conn->query("SELECT G.id FROM {$dbt_games} G ORDER BY G.id DESC LIMIT 1");
$last_game = $id_last_game->fetch_assoc();

if (isset($_GET['id'])) {
    $id_game = (int) $_GET['id'];
} else {
    $id_game = (int) $last_game['id'];
}

// получаем статус о игре за 3-е место
$bronze = $conn->query("SELECT bronze FROM {$dbt_games} WHERE id = {$id_game}")->fetch_assoc();

$sql = "SELECT F.id, t1.team t1, t2.team t2, F.round, F.block, F.next_block, F.next_block_position, F.id_t1, F.id_t2 
    FROM {$dbt_final} F
    INNER JOIN {$dbt_teams} t1 ON t1.id = IFNULL(F.id_t1, 1)
    INNER JOIN {$dbt_teams} t2 ON t2.id = IFNULL(F.id_t2, 1)
    WHERE F.id_game = {$id_game} ORDER BY id ASC";
$final = $conn->query("{$sql}");

echo "<div id='final'>";
if (null === $login) {
    die('Будник не одобряет');
}

$id_last_round = $conn->query("SELECT round, block FROM {$dbt_final}
                                WHERE id_game = {$id_game} ORDER BY round DESC LIMIT 1");
$last_round = $id_last_round->fetch_assoc();

echo "<table style='margin:5px;'><tr>";

    for ($i = 1; $i <= $last_round['round']; ++$i) {
        echo "<td style='width: 183px; text-align: center'>";

        if (1 === (int) $bronze['bronze']) {
            if ($i === (int) $last_round['round']) {
                echo 'Игра за 3-е место';
            } elseif ($i === (int) $last_round['round'] - 1) {
                echo 'Финал';
            } else {
                echo "Раунд {$i}";
            }
        } else {
            if ($i === (int) $last_round['round']) {
                echo 'Финал';
            } else {
                echo "Раунд {$i}";
            }
        }

        echo '</td>';
    }

echo '</tr></table>';

for ($i = 1; $i <= $last_round['round']; ++$i) {
    // Cчетчик стиля id=even
    $e = $i;

    if (1 === (int) $bronze['bronze']) {
        // Cчетчик стиля id=even для игр 12 и 24
        if (12 == $final->num_rows or 24 == $final->num_rows) {
            if ($i >= 2) {
                $e = $i - 1;
            }
        }

        if ($i == $last_round['round']) {
            echo "<div id='final".$final->num_rows."'>";
            echo "<div style='margin: 5px 0px'>Игра за 3-е место:</div>";
        } elseif ($last_round['round'] - 1 == $i) {
            echo "<div id='final".$final->num_rows."'>";
            echo "<div style='margin: 5px 0px'>Финал:</div>";
        } else {
            echo "<div id='even".$e."'>";
        }
    } else {
        if (11 == $final->num_rows or 23 == $final->num_rows) {
            if ($i >= 2) {
                $e = $i - 1;
            }
        }

        if ($i == $last_round['round']) {
            $col = $final->num_rows + 1;
            echo "<div id='final".$col."'>";
            echo "<div style='margin: 5px 0px'>Финал:</div>";
        } else {
            echo "<div id='even".$e."'>";
        }
    }

    // Выводим команды и счет
    $j = 1;
    foreach ($final as $value) {
        $match_id = $value['id'];
        if ($value['round'] == $i) {
            // Оперделяем кто победил
            $grand_score = $conn->query("SELECT id, s1, s2 FROM {$dbt_final_score} WHERE id_match = {$match_id} AND id_game = {$id_game}");
            $t1 = null;
            $t2 = null;
            foreach ($grand_score as $gs) {
                if ($gs['s1'] > $gs['s2']) {
                    ++$t1;
                } else {
                    ++$t2;
                }
            }

            $url = "<a href='?id=".$id_game.'&id_match='.$value['id'].'&round='.$value['round'].'&position='.$j.'&block='.$value['block'].'&next_block='.$value['next_block'].'&next_block_position='.$value['next_block_position'].'&id_t1='.$value['id_t1'].'&id_t2='.$value['id_t2'].'&t1='.htmlspecialchars($value['t1'], ENT_QUOTES).'&t2='.htmlspecialchars($value['t2'], ENT_QUOTES)."#finalEdit'>";
            $img_edit = "<img width='13px' src='http://".$_SERVER['HTTP_HOST']."/img/edit_score.png'>";

            // Команда 1
            echo "<div class='final -clear'><a name=".$j."></a>".$value['t1'].'</div>';

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
        $j++;
    }

    if ($i == $last_round['round']) {
        echo "<form action='../func/statistics_game.php' method='POST'>
            <input type='hidden' name='id' value='".$id_game."'>
            <input class ='submit -addteam' type='submit' value='Завершить турнир'>
        </form>";
    }

    echo '</div>';
}

echo '</div>';

// ---------------Модальное окно------------------

$id_match = $_GET['id_match'] ?? 0;

$grand = $conn->query("SELECT id, s1, s2 FROM {$dbt_final_score} WHERE id_match = {$id_match} AND id_game = {$id_game}");

echo "<div id='finalEdit' class='modalGrand'>
<div><a href='http://".$_SERVER['HTTP_HOST']."/admin/final.php?id={$id_game}#close' title='Закрыть' class='close'>x</a>
    <h3>Результаты матча</h3>
    <form action='http://".$_SERVER['HTTP_HOST']."/func/final_game.php' method='POST'>
        <input type='hidden' name='id_match' value='".$id_match."'>
        <input type='hidden' name='id_game'  value='".$id_game."'>
        <input type='hidden' name='round'  value='" . ($_GET['round'] ?? 0) . "'>
        <input type='hidden' name='block'  value='".  ($_GET['block'] ?? 0) . "'>
        <input type='hidden' name='position'  value='" . ($_GET['position'] ?? 0) . "'>
        <input type='hidden' name='next_block'  value='" . ($_GET['next_block'] ?? 0) . "'>
        <input type='hidden' name='next_block_position'  value='" . ($_GET['next_block_position'] ?? 0) . "'>
        <input type='hidden' name='id_t1'  value='". ($_GET['id_t1'] ?? 0) . "'>
        <input type='hidden' name='id_t2'  value='". ($_GET['id_t2'] ?? 0) . "'>
        <input type='hidden' name='t1'  value='". ($_GET['t1'] ?? 0) . "'>
        <input type='hidden' name='t2'  value='". ($_GET['t2'] ?? 0) . "'>

        <table>
        <tr>
            <td colspan='6' style='border: 0px; background: #2B303D';>
                <div class='counter' onclick='addInput()'>добавить</div><div class='counter' onclick='delInput()'>удалить</div>
            </td>
        </tr>
        <tr>
            <td>". ($_GET['t1'] ?? "") ."</td>
            <td>
                <div id='profile'> ";
                        if($grand){
                            $i = 0;
                            foreach ($grand as $val1) {
                                ++$i;
                                echo "<div id='input$i' data-id='{$val1['id']}'>";
                                echo "<input type='number' autocomplete='off' class='form-grand' name='us1[]' value='{$val1['s1']}'>";
                                echo '</div>';
                            }
                        }

                        if (0 == $grand->num_rows && $i <= 4) {
                            echo "<div id='input1'>";
                            echo "<input type='number' autocomplete='off' class='form-grand' name='is1[]'>";
                            echo "</div>";
                        }

                echo '</div>
            </td>
        </tr>
        <tr>
            <td>'. ($_GET['t2'] ?? "") ."</td>
            <td>
                <div id='profile2'>";
                    if($grand){
                        $i = 0;
                        foreach ($grand as $val2) {
                            ++$i;
                            echo "<div id='input$i'>";
                            echo "<input type='number' autocomplete='off' class='form-grand' name='us2[]' value='{$val2['s2']}'>";
                            echo '</div>';
                        }
                    }

                    if (0 == $grand->num_rows && $i <= 4) {
                        echo "<div id='input1'>";
                        echo "<input type='number' autocomplete='off' class='form-grand' name='is2[]'>";
                        echo "</div>";
                    }
               echo '</div>       
            </td>
        </tr>';
echo "</table>
    <input class ='submit' type='submit' value='Сохранить'></form>
</div>
</div>";
