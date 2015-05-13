<?php

$content = $where . $cookie ;
$id = $content;

$id = isset($id)? $_GET['id']:2; //
$sql = "xxx". $id ;
mysql_query($sql) ;

?>