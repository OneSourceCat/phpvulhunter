<?php

/**
 * HTML5文件上传类
 * $Author: pengwenfei p@simple-log.com
 * $Date: 2011-02-05
 * www.simple-log.com 
*/

if (!defined('IN_PBBLOG'))
{
	die('Access Denied');
}


class cls_upload {

	var $error='';

	function upload($upload,$type='img')
	{

		//对html5文件上传方式进行处理
		if(isset($_SERVER['HTTP_CONTENT_DISPOSITION']))
		{
			if(preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info))
			{
				$temp_name=ini_get("upload_tmp_dir").'\\'.time().'.tmp';
				file_put_contents($temp_name,file_get_contents("php://input"));
				$size=filesize($temp_name);
				$upload_arr=array('name'=>$info[2],'tmp_name'=>$temp_name,'size'=>$size,'type'=>'','error'=>0);
			}

			//对上传类别判断
			if (!$this->check_file($upload_arr))
			{
				$this->error='此文件类别不允许上传';
				return false;
			}

			$dir=PBBLOG_ROOT.'home/upload/'.date('Y-m').'/';
			$url_dir='home/upload/'.date('Y-m').'/';

			//文件按照月份存档，如果不存在该目录则创建该目录
			if (!is_dir($dir))
			{
			    if (!mkdir($dir))
			    {
			        $this->error='创建文件上传目录失败';
			        
			    }
			}

			//生成文件名
			$file_name=time().rand(10000,60000).'.'.substr($upload_arr['name'], strrpos($upload_arr['name'], '.')+1);
			$url_name=$url_dir.$file_name;
			$file_name=$dir.$file_name;

			//rename($upload_arr['tmp_name'],$file_name);
			//@chmod($file_name,0755);
			return $url_name;
		}
		
	}

	
}
?>