<?php

require 'source.php';

$id = $_GET['id'] ;
$db = new MYSQLDB() ;

$sql = "xxx". $id ;
$db->query($sql) ;

?>