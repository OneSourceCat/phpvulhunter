<?php

$id = $_GET['id'] ;
$id = addslashes($id) ;
if($id){
	echo $id ;
}else if($id>0){
	echo false;
}else{
	if(1){
		echo "xxxx" ;
	}
	echo "shit";
}

$sql = "xxx". $id ;
mysql_query($sql) ;

?>