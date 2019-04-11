<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if ($login != null){

    $id_game = (int)$_GET['id'];

    if($id_game > 0){
        $edit_view = $conn->query("SELECT teams.team, games.game, qualification.id_team, qualification.id_game FROM qualification
        INNER JOIN teams ON teams.id = qualification.id_team
        RIGHT JOIN games ON games.id = qualification.id_game
        WHERE games.id = $id_game");
        $game = $edit_view->fetch_assoc();

        /* Редактирование турнира */
        echo "<div id='create-block'>
            <form action='http://".$_SERVER['HTTP_HOST']."/func/shuffle_team.php' method='POST' style='float: left; margin-bottom: 5px'>
                <input type='hidden' name='start_game' value='".$id_game."'>";
                if ($edit_view->num_rows >= 4){
                    echo "<input class ='submit -addteam' type='submit' value='НАЧАТЬ НОВЫЙ ТУРНИР'>";
                }else{
                    echo "<input class ='submit -addteam -dis' type='submit' value='НАЧАТЬ НОВЫЙ ТУРНИР' disabled>";
                }      
            echo "</form>

                <form action='http://".$_SERVER['HTTP_HOST']."/func/shuffle_team.php' method='POST' style='float: left; margin-bottom: 5px'>
                <input type='hidden' name='start_game' value='".$id_game."'>
                &nbsp<input class ='submit -addteam' type='submit' value='РЕДАКТИРОВАТЬ ТЕКУЩИЙ ТУРНИР'>
                </form>";  

            echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/edit_game.php' method='POST' style='float: right;'>
                <input type='hidden' name='del_game' value='".$id_game."'>
                <input class ='submit -addteam' type='submit' value='УДАЛИТЬ ТУРНИР'><br>
            </form>";

            if ($game['team'] === NULL){
                echo "<p style='margin: 5px 0;clear: both;'>В турнире нет команд</p>";
            }else{
                echo "<p style='margin: 5px 0;clear: both;'>В турнире зарегестрированно ". $edit_view->num_rows . getNumEnding($edit_view->num_rows, array(' команда', ' команды', ' команд')). "</p>";
            }
            
            echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/edit_game.php' method='POST'>
                <input type='hidden' name='upd_game' value='".$id_game."'>
                Турнир: <input class='input-team' type='text' name='game' value='".$game['game']."'>  
                <input class ='submit -addteam' type='submit' value='ИЗМЕНИТЬ'><br>
            </form>
                Добавить команду:
                <input type='text' class='team input-team' placeholder='Название команды' autocomplete='off'>
                <input type='hidden' id='game' value='".$id_game."'>
                <div class='search_create'></div>";
                if($_GET['mes'] == 'err'){
                    echo "<p style='color:red'>Данная команда уже зарегистрирована в турнире!!</p>";
                }
        echo "</div>";

        /* Таблица команд */
        echo "<div id='create-block'>";
            echo "<table>";
                echo "<tr>";
                    echo "<td>№</td>
                        <td colspan='3' align='center'>Команды участники</td>";
                        echo "</tr>";
                        $i = 1;
                        foreach($edit_view as $value){
                            if($value['team'] == NULL){
                                echo "<tr>";
                                echo "
                                <td colspan='3' align='center' style='min-width: 280px;'>Добавьте команды!</td>";
                                echo "</tr>";
                            }else{
                                echo "<tr>";
                                echo "<td align='center'>".$i++."</td>";
                                echo "<td style='min-width: 200px;'>".$value['team']."</td>";
                                
                                echo "<form action='#' method='POST'>
                                    <input type='hidden' name='change_team' value='".$value['id_team']."'>
                                    <input type='hidden' name='team' value='".$value['team']."'>
                                    <input type='hidden' name='id_game' value='".$value['id_game']."'>
                                    <td style='padding: 2px 10px;'><input class ='submit -addteam -del' type='submit' value='Заменить'></td>
                                    </form>";

                                echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/edit_game.php' method='POST'>
                                    <input type='hidden' name='del_team' value='".$value['id_team']."'>
                                    <input type='hidden' name='id_game' value='".$value['id_game']."'>
                                    <td style='padding: 2px 10px;'><input class ='submit -addteam -del' type='submit' value='X'></td>
                                </form>";
                            }
                        }
                echo "</tr>";
            echo "</table>";
        echo "</div>";

        /* Замена команды */
        if($_POST['change_team'] != NULL){
            
            echo "<div id='create-block'>";
                echo "Заменить <b>{$_POST['team']}</b> на:<br>";
                echo "Выберите команду:
                <input type='text'  class='change_team input-team' placeholder='Название команды' autocomplete='off'>
                <input type='hidden' id='team' value='".$_POST['change_team']."'>
                <input type='hidden' id='game' value='".$_POST['id_game']."'>
                <div class='search_change_team'></div>";
            echo "</div>";
        }
    }else{
        die;
    }
}else{
    echo "Доступ запрещен!";
}
echo "</div>";
?>