<?php
ob_start();?><!DOCTYPE html>
<html lang={doc:lang}>
	<head>
		<meta charset=utf-8>
		<title>{title}</title>
	</head>
	<body>
		<header>
			<h1>{title}</h1>
		</header>
		<div class=content>
			{content}
		</div>
		<footer>
		</footer>
	</body>
</html><?php $GLOBALS['HTML_Template'] = ob_get_clean();

require_once 'lib/html.php';
class default_html extends html {
};

return new default_html($this);
?>
