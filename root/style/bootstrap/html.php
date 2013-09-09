<?php
ob_start();?><!DOCTYPE html>
<html lang={doc:lang}>
	<head>
		<meta charset=utf-8>
		<meta name=viewport content="width=device-width, initial-scale=1.0">
		<title>{title}</title>
		<link href="{style}css/bootstrap.css" rel=stylesheet>
		<link href="{style}common.css" rel=stylesheet>
		<link href="{style}css/bootstrap-responsive.css" rel="stylesheet">
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="{style}js/html5shiv.js"></script>
		<![endif]-->

		<!-- Fav and touch icons -->
		<link rel=apple-touch-icon-precomposed sizes=144x144 href="{style}ico/apple-touch-icon-144-precomposed.png">
		<link rel=apple-touch-icon-precomposed sizes=114x114 href="{style}ico/apple-touch-icon-114-precomposed.png">
		<link rel=apple-touch-icon-precomposed sizes=72x72 href="{style}ico/apple-touch-icon-72-precomposed.png">
		<link rel=apple-touch-icon-precomposed href="{style}ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="{style}ico/favicon.png">
	</head>
	<body>
		{area:navbar}
				<div class="nav-collapse collapse">
					<ul class=nav>
						<li class=active><a href="{root}">Inicio</a></li>
						<li><a href="{root}acerca">Acerca de</a></li>
						<li><a href="{root}contacto">Contacto</a></li>
					</ul>
				</div><!--/.nav-collapse -->

		<div class="container">
			<h1>{title}</h1>
			{content}
		</div> <!-- /container -->

		<script src="{style}js/jquery.js"></script>
		<script src="{style}js/bootstrap-transition.js"></script>
		<script src="{style}js/bootstrap-alert.js"></script>
		<script src="{style}js/bootstrap-modal.js"></script>
		<script src="{style}js/bootstrap-dropdown.js"></script>
		<script src="{style}js/bootstrap-scrollspy.js"></script>
		<script src="{style}js/bootstrap-tab.js"></script>
		<script src="{style}js/bootstrap-tooltip.js"></script>
		<script src="{style}js/bootstrap-popover.js"></script>
		<script src="{style}js/bootstrap-button.js"></script>
		<script src="{style}js/bootstrap-collapse.js"></script>
		<script src="{style}js/bootstrap-carousel.js"></script>
		<script src="{style}js/bootstrap-typeahead.js"></script>
	</body>
</html><?php $GLOBALS['HTML_Template'] = ob_get_clean();

require_once 'lib/html.php';
class bootstrapml extends html {
	public $style='/style/bootstrap/';
	public $root='/';
	
	function __construct($page) {
		html::__construct($page);
		$nb = $this->area_set('navbar');
	}
};

$GLOBALS['ilm2html_subs'] = array_merge($GLOBALS['ilm2html_subs'],array(
	'navbar'=>array(2,'div','navbar'),
	'navbar-inner'=>array(2,'div','navbar-inner'),
	'container'=>array(2,'div','container'),
	'btn'=>array(2,'button','btn'),
	'icon-bar'=>array(0,'span','icon-bar'),
	'nav-collapse'=>array(2,'div','nav-collapse'),
));

return new bootstrapml($this);
?>
