<?php

$content = $_GET['id'] ;
$id = $content ;
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

print $sql ;

?>