<?php
include_once("../includes/global.php"); 
	$script_tmp = explode('/', $_SERVER['SCRIPT_NAME']);
	$sctiptName = array_pop($script_tmp);
	include_once("auth.php");

	if(empty($_POST))
	{
		$refer_lang = $_GET['code'] =='en'?'cn':'en';	//基本参照语言
		$l = $rl = array();echo "start";
		@include_once($config['webroot'].'/module/'.$_GET['mod'].'/lang/'.$_GET['code'].'.php');

		@eval('$l =$_LANG_MOD_'.strtoupper($_GET['mod']).';');	
		@include_once($config['webroot'].'/module/'.$_GET['mod'].'/lang/'.$refer_lang.'.php');
		@eval('$rl =$_LANG_MOD_'.$_GET['mod'].';');

		$diff_lang = @array_diff_key($rl,$l);
		$l += $diff_lang; 
		if($l=='')
			die($lang['translat_data_emp']);
	}else{
		   if($config['enable_tranl']==0)
		  {
				die($lang['tranl_fordid']);
		  }
			include_once("../includes/lang_class.php");
			$tr_lang = new lang();
			foreach($tr_lang->module_files() as $key=>$mod)
			{	
				if(isset($_POST[strtolower($mod)]))
				{	
					$tr_lang->save_module_files( $_POST[strtolower($mod)],$key,$_GET['code'] );
					echo "<script>parent.window.succ_trans_tip('$key');</script>";
					break;
				}
			}
			die();
	}
?>

