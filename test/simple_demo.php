<?php

$sql = "select * from user where id=" .$_GET['id'] ;
$sql = base64_encode($sql) ;
$sql = base64_decode($sql) ;
$clearsql = addslashes($sql) ;

?>