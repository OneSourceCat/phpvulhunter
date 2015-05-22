<?php /* Smarty version 3.1.23, created on 2015-05-21 13:34:13
         compiled from "views/template/index.html" */ ?>
<?php
/*%%SmartyHeaderCode:8903555dc2b54928d2_53651343%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '43a19e80fd7d836368551f0e7a43a5674faec54d' => 
    array (
      0 => 'views/template/index.html',
      1 => 1432182970,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8903555dc2b54928d2_53651343',
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_555dc2b5696354_18252969',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_555dc2b5696354_18252969')) {
function content_555dc2b5696354_18252969 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '8903555dc2b54928d2_53651343';
?>
<!doctype html>
<html lang="en">
<?php echo $_smarty_tpl->getSubTemplate ('header.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

<body>
	<?php echo $_smarty_tpl->getSubTemplate ('navigation.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>


	<div class="content">
		<div id="err_cont">
			<a href="javascript:;">OK</a>
		</div>

		<div class="content-panel">
		</div>
	</div>
	<div class="waiting">
		<div class="loading"></div>
		<div class="load-font">
			<div class="wait-font">
				Scanning
			</div>
			<div class="jumppoint">
				<span id="p1">.</span><span id="p2">.</span><span id="p3">.</span>
			</div>
		</div>
	</div>
	<?php echo $_smarty_tpl->getSubTemplate ('footer.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

</body>
</html><?php }
}
?>