<?php

require_once '/a.php';
require_once './source.php';
require_once '../../b.php';

function goods_del($s,$id)
{
	$a = $id;
	del2($a,$b);
	$d = mysql_query($s);
	
}

function del2($where,$hi)
{
	$where = ' WHERE '.$where;
	$sql = 'DELETE FROM '.$where;
	$sql = jinghua($sql1,$hi);
	$sql = $sql;
	return mysql_query($sql);
}

function jinghua($str,$str1){
    $str1 = addslashes($str1);
    return addslashes($str);
}


goods_del($id) ;
?>