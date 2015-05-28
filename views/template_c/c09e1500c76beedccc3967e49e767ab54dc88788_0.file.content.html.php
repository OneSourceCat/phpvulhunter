<?php /* Smarty version 3.1.23, created on 2015-05-27 09:17:14
         compiled from "views/template/content.html" */ ?>
<?php
/*%%SmartyHeaderCode:624755658b9aeb16f4_48217187%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c09e1500c76beedccc3967e49e767ab54dc88788' => 
    array (
      0 => 'views/template/content.html',
      1 => 1432513732,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '624755658b9aeb16f4_48217187',
  'variables' => 
  array (
    'results' => 0,
    'n' => 0,
    'xss' => 0,
    'sqli' => 0,
    'http' => 0,
    'code' => 0,
    'exec' => 0,
    'ldap' => 0,
    'include' => 0,
    'file' => 0,
    'xpath' => 0,
    'fileaffect' => 0,
    'unserialize' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_55658b9b166367_52451000',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_55658b9b166367_52451000')) {
function content_55658b9b166367_52451000 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '624755658b9aeb16f4_48217187';
$_smarty_tpl->tpl_vars["sqli"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["xss"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["http"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["code"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["exec"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["ldap"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["include"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["file"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["xpath"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["fileaffect"] = new Smarty_Variable(0, null, 0);?>
<?php $_smarty_tpl->tpl_vars["unserialize"] = new Smarty_Variable(0, null, 0);?>
<ul id="content-box">
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
		<div class="content-text">
			<div>
				<h4>File Path : </h4>
					<span><?php echo $_smarty_tpl->tpl_vars['n']->value['node_path'];?>
</span>
			</div>
			<div>
				<h4>Vlun Type : </h4>
					<span><?php echo $_smarty_tpl->tpl_vars['n']->value['type'];?>
</span>
			</div>
			<div class="node">
				<h4>Sink Call : </h4><br/>
				<h5>File : </h5>
					<span><?php echo $_smarty_tpl->tpl_vars['n']->value['node_path'];?>
</span><br/>
				<h5>Line : </h5>
					<h6>start line : </h6><span><?php echo substr($_smarty_tpl->tpl_vars['n']->value['node']['line'],0,stripos($_smarty_tpl->tpl_vars['n']->value['node']['line'],'|'));?>
</span>
					<h6>end line : </h6><span><?php echo substr($_smarty_tpl->tpl_vars['n']->value['node']['line'],(stripos($_smarty_tpl->tpl_vars['n']->value['node']['line'],'|')+1));?>
</span><br/>
				<h5>Code : </h5>
					<span><?php echo $_smarty_tpl->tpl_vars['n']->value['node']['code'];?>
</span><br/><!--index 5-->
			</div>
			<div class="var">
				<h4>Sensitive Arg : </h4><br/>
				<h5>File : </h5>
					<span><?php echo $_smarty_tpl->tpl_vars['n']->value['var_path'];?>
</span><br/>
				<h5>Line : </h5>
					<h6>start line : </h6><span><?php echo substr($_smarty_tpl->tpl_vars['n']->value['var']['line'],0,stripos($_smarty_tpl->tpl_vars['n']->value['var']['line'],'|'));?>
</span>
					<h6>end line : </h6><span><?php echo substr($_smarty_tpl->tpl_vars['n']->value['var']['line'],(stripos($_smarty_tpl->tpl_vars['n']->value['var']['line'],'|')+1));?>
</span><br/>
				<h5>Code : </h5>
					<span><?php echo $_smarty_tpl->tpl_vars['n']->value['var']['code'];?>
</span><br/>
			</div>
		</div>
		<div class="code-box">
			<a href="javascript:;">Code Viewer</a>
			<div class="code"></div>
		</div>			
	</li>
	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='XSS') {?>
		<?php $_smarty_tpl->tpl_vars['xss'] = new Smarty_Variable($_smarty_tpl->tpl_vars['xss']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='SQLI') {?>
		<?php $_smarty_tpl->tpl_vars['sqli'] = new Smarty_Variable($_smarty_tpl->tpl_vars['sqli']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='HTTP') {?>
		<?php $_smarty_tpl->tpl_vars['http'] = new Smarty_Variable($_smarty_tpl->tpl_vars['http']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='CODE') {?>
		<?php $_smarty_tpl->tpl_vars['code'] = new Smarty_Variable($_smarty_tpl->tpl_vars['code']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='EXEC') {?>
		<?php $_smarty_tpl->tpl_vars['exec'] = new Smarty_Variable($_smarty_tpl->tpl_vars['exec']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='LDAP') {?>
		<?php $_smarty_tpl->tpl_vars['ldap'] = new Smarty_Variable($_smarty_tpl->tpl_vars['ldap']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='INCLUDE') {?>
		<?php $_smarty_tpl->tpl_vars['include'] = new Smarty_Variable($_smarty_tpl->tpl_vars['include']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='FILE') {?>
		<?php $_smarty_tpl->tpl_vars['file'] = new Smarty_Variable($_smarty_tpl->tpl_vars['file']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='XPATH') {?>
		<?php $_smarty_tpl->tpl_vars['xpath'] = new Smarty_Variable($_smarty_tpl->tpl_vars['xpath']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='FILEAFFECT') {?>
		<?php $_smarty_tpl->tpl_vars['fileaffect'] = new Smarty_Variable($_smarty_tpl->tpl_vars['fileaffect']->value+1, null, 0);?>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['n']->value['type']=='UNSERIALIZE') {?>
		<?php $_smarty_tpl->tpl_vars['unserialize'] = new Smarty_Variable($_smarty_tpl->tpl_vars['unserialize']->value+1, null, 0);?>
	<?php }?>
<?php
$_smarty_tpl->tpl_vars['n'] = $foreachItemSav;
}
?>
<?php }?>
</ul>

<?php if (isset($_smarty_tpl->tpl_vars['results']->value)) {?>
<div class="count-box">
	<span>Total : </span>
		<abbr title="<?php echo count($_smarty_tpl->tpl_vars['results']->value);?>
"><?php echo count($_smarty_tpl->tpl_vars['results']->value);?>
</abbr>

	<?php if ($_smarty_tpl->tpl_vars['xss']->value!=0) {?>
	<span>XSS : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['xss']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['xss']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['sqli']->value!=0) {?>
	<span>SQLI : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['sqli']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['sqli']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['http']->value!=0) {?>
	<span>HTTP : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['http']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['http']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['code']->value!=0) {?>
	<span>CODE : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['code']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['code']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['exec']->value!=0) {?>
	<span>EXEC : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['exec']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['exec']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['ldap']->value!=0) {?>
	<span>LDAP : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['ldap']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['ldap']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['include']->value!=0) {?>
	<span>INCLUDE : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['include']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['include']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['file']->value!=0) {?>
	<span>FILE : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['file']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['file']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['xpath']->value!=0) {?>
	<span>XPATH : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['xpath']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['xpath']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['fileaffect']->value!=0) {?>
	<span>FILEAFFECT : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['fileaffect']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['fileaffect']->value;?>
</abbr>
	<?php }?>

	<?php if ($_smarty_tpl->tpl_vars['unserialize']->value!=0) {?>
	<span>UNSERIALIZE : </span>
		<abbr title="<?php echo $_smarty_tpl->tpl_vars['unserialize']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['unserialize']->value;?>
</abbr>
	<?php }?>
</div>
<?php }?><?php }
}
?>