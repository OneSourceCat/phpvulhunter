<?php

function goods_del($id)
{
	$a = $id;
	if(is_array($id))
	{
		foreach($id as $key => $val)
		{
			del2($val);
		}
	}

}


function del2($where)
{
	$where = ' WHERE '.$where;
	$sql   = 'DELETE FROM '.$this->tableName.$where;
	return mysql_query($sql);
}

//main file
$id = $_POST['id'] .$a ."b" ;
goods_del($id) ;

?>












