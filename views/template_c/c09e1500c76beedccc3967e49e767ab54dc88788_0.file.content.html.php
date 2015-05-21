<?php /* Smarty version 3.1.23, created on 2015-05-21 03:33:31
         compiled from "views/template/content.html" */ ?>
<?php
/*%%SmartyHeaderCode:9855555d520b7c98d7_13860838%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c09e1500c76beedccc3967e49e767ab54dc88788' => 
    array (
      0 => 'views/template/content.html',
      1 => 1432119098,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '9855555d520b7c98d7_13860838',
  'variables' => 
  array (
    'results' => 0,
    'n' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_555d520b913b88_43136928',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_555d520b913b88_43136928')) {
function content_555d520b913b88_43136928 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '9855555d520b7c98d7_13860838';
?>

	
		<ul>
		<?php if (isset($_smarty_tpl->tpl_vars['results']->value)) {?>
		<?php
$_from = $_smarty_tpl->tpl_vars['results']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['n'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['n']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['n']->value) {
$_smarty_tpl->tpl_vars['n']->_loop = true;
$foreachItemSav = $_smarty_tpl->tpl_vars['n'];
?>
			<li>
				<h2>Find a vulnerability !</h2>
				<div>file path : <?php echo $_smarty_tpl->tpl_vars['n']->value['path'];?>
</div>
				<div>vlun type : <?php echo $_smarty_tpl->tpl_vars['n']->value['type'];?>
</div>
				<div>node : <?php echo $_smarty_tpl->tpl_vars['n']->value['node'];?>
</div>
				<div>var : <?php echo $_smarty_tpl->tpl_vars['n']->value['var'];?>
</div>				
			</li>
		<?php
$_smarty_tpl->tpl_vars['n'] = $foreachItemSav;
}
?>
		<?php }?>
		</ul>

<?php }
}
?>