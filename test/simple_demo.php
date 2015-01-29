<?php

$name = $_GET['name'] ;
$sql = "select * from User where name='{$name}'" ;
mysql_query($sql) ;

?>