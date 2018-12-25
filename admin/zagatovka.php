<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if ($login != null)
{
    
}
echo "</div>";