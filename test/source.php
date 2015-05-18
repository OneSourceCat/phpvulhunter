<?php 

class MYSQLDB{
	function query($sql){
		mysql_query($sql) ;
	}
	function get_one(){
		echo 'hello' ;
	}
}


?>