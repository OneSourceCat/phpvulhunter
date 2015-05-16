<?php

require 'source.php';

$content = $where ;
$id = $content ;
if($id){
	echo 111 ;
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