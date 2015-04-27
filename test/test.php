<?php
$where = "f4cky0u" ;
$id = $where . $_GET['id'] ;

while($id){
	echo "1" ;
}

$id = "xxxxxxxx".$id ;
$sql = "select * from User where id=" .$id ;

mysql_query($sql) ;

?>