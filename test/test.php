<?php

$id = $_GET['id'] ;
$where = "order by" ;
$id = addslashes($id) ;
$sql = "xxx$where". $id ;
mysql_query($sql) ;

?>