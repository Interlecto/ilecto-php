<?php
if(!isset($IL_Install_Query)) {
	header(' ',false,403);
	die('You cannot install from this file!');
	return false;
}
function __ep($a,$b) { return empty($_POST[$a])?$b:$_POST[$a]; }
function __up($a,$b) { return isset($_POST[$a])?$_POST[$a]:$b; }
function __ip($a,$b) { return isset($_POST[$a]) && $_POST[$a]!=$b; }
$comment = '';
if(!empty($_POST)) {
	$comment = "<pre>".print_r($_POST,true)."</pre>\n";
	if(isset($_POST['sitename'])) {
		$s = "<?php\n";
		$s.= "\$il_site_name = '{$_POST['sitename']}';\n";
		if(__ip('dbhost','localhost'))
			$s.= "\$il_data_server = '{$_POST['dbhost']}';\n";
		if(__ip('dbname','interlecto'))
			$s.= "\$il_data_database = '{$_POST['dbname']}';\n";
		if(__ip('dbprefix',''))
			$s.= "\$il_data_prefix = '{$_POST['dbprefix']}';\n";
			if(__ip('dbuser','root'))
			$s.= "\$il_data_user = '{$_POST['dbuser']}';\n";
		if(__ip('dbpass',''))
			$s.= "\$il_data_password = '{$_POST['dbpass']}';\n";
		$s.= "?>";
		file_put_contents('site_def.php',$s);
		header('Location: install.php');
		die('Go!');
		$comment = "<pre>".htmlspecialchars($s)."</pre>\n";
	}
}
$comment.= "<p class=\"alert alert-warning\">$IL_Install_Query</p>";
?><!DOCTYPE html>
<html>
<head>
<title>Initial configuration of this Interlecto site</title>
<meta name=viewport content="width=device-width, initial-scale=1.0">
<link rel=stylesheet media=screen href="style/bootstrap/css/bootstrap.css">
</head>
<body>
<div class=container>
<div class=jumbotron>
<h1>Initial configuration</h1>
</div>
<?php if(isset($comment)) echo $comment; ?>
<form method=post class=form-horizontal role=form>
	<div class=form-group>
		<label for=sitename class="col-lg-3 control-label">Site name:</label>
		<div class=col-lg-7>
			<input name=sitename id=sitename class=form-control value="<?=__ep('sitename','')?>" placeholder="This site's name">
		</div>
	</div>
	<div class=form-group>
		<label for=dbhost class="col-lg-3 control-label">Databse server:</label>
		<div class=col-lg-7>
			<input name=dbhost id=dbhost class=form-control value="<?=__ep('dbhost','localhost')?>" placeholder="DB server address">
		</div>
	</div>
	<div class=form-group>
		<label for=dbname class="col-lg-3 control-label">Database name:</label>
		<div class=col-lg-7>
			<input name=dbname id=dbname class=form-control value="<?=__ep('dbname','interlecto')?>" placeholder="Name of the DB">
		</div>
	</div>
	<div class=form-group>
		<label for=dbprefix class="col-lg-3 control-label">Database prefix:</label>
		<div class=col-lg-7>
			<input name=dbprefix id=dbprefix class=form-control value="<?=__up('dbprefix','il_')?>" placeholder="Prefix">
		</div>
	</div>
	<div class=form-group>
		<label for=dbuser class="col-lg-3 control-label">Database user name:</label>
		<div class=col-lg-7>
			<input name=dbuser id=dbuser class=form-control value="<?=__ep('dbuser','root')?>" placeholder="Login name in DB">
		</div>
	</div>
	<div class=form-group>
		<label for=dbpass class="col-lg-3 control-label">Database user password:</label>
		<div class=col-lg-7>
			<input name=dbpass id=dbpass class=form-control type=password>
		</div>
	</div>
	<div class=form-group>
		<div class="col-lg-offset-3 col-lg-7">
			<input type=submit value="Send configuration" class=btn>
		<div>
	</div>
</form>
</div>
<script src="//code.jquery.com/jquery.js"></script>
<script src="style/bootstrap/js/bootstrap.min.js"></script>
</body>
</html><?php return true;?>
