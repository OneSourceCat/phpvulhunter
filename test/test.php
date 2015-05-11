<?php

$content = urldecode($where) ;
$id = $content;
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
$sql = addslashes($sql) ;
mysql_query($sql) ;

?>