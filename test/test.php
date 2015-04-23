<?php

$where = "f4cky0u" ;
$id = $where . $_GET['id'] ;

if($id){
	echo 11;
}else{
	echo 22;
}

$sql = "select * from User where id=" .$id ;
$sql = htmlentities($sql) ;
$sql = stripcslashes($sql) ;
$sql = addslashes($sql) ;
mysql_query($sql) ;

?>