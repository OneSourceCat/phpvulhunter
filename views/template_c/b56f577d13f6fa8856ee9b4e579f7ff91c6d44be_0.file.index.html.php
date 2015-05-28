<?php /* Smarty version 3.1.23, created on 2015-05-28 09:45:42
         compiled from "views/template/index.html" */ ?>
<?php
/*%%SmartyHeaderCode:38475566e3c66efcd7_72056886%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b56f577d13f6fa8856ee9b4e579f7ff91c6d44be' => 
    array (
      0 => 'views/template/index.html',
      1 => 1432806340,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '38475566e3c66efcd7_72056886',
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_5566e3c672c5f9_08962120',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5566e3c672c5f9_08962120')) {
function content_5566e3c672c5f9_08962120 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '38475566e3c66efcd7_72056886';
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
			<?php echo $_smarty_tpl->getSubTemplate ('content.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

		</div>
	</div>
	<div class="waiting">
		<div class="wait-left">
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
		<div class="div-line"></div>
		<div class="wait-right">
			<div class="timecounter">
				<span id="h"></span>:<span id="m"></span>:<span id="s"></span>:<span id="ms"></span>
			</div>
		</div>
	</div>
	<?php echo $_smarty_tpl->getSubTemplate ('footer.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

</body>
</html><?php }
}
?>