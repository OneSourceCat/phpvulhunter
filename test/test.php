<?php

$id = $_GET['id'] ;
$where = "order by" ;

isset($sql)? $sql = "xxx$where". $id:'' ;
mysql_query($sql) ;

?>