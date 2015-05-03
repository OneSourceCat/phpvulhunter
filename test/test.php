<?php

$id = $_GET['id'] ;
if($id){
	echo $id ;
}else{
	$id = addslashes($id) ;
}

$sql = "xxx". $id ;
mysql_query($sql) ;


?>