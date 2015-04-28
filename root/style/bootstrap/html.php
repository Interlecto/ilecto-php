<?php ob_start()?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{page:title}</title>
<link rel="stylesheet" href="{style}css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="{style}custom.css" type="text/css" />
<link rel="icon" href="{page:site:icon}">
</head><!-- {page:type} -->
<body class="{page:class}">
  {area:header}

 <div class="container">
  <div class="content" id="body">
    <h1 id="title"><a href="">{page:title}</a></h1>
    {content}
  </div>
 </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="{style}js/bootstrap.min.js"></script>
</body>
</html>
<?php $GLOBALS['HTML_Template'] = ob_get_clean();

require_once 'lib/html.php';
class ilui_html extends html {
	public $style = '/style/bootstrap/';
	function __construct($page) {
		html::__construct($page);
		#il_set('site/motto','Tu interlocución y presencia en nuevos paradigmas sociales',SET_EMPTY);
		header('Content-type: text/html;charset=utf-8');
		$this->area_set('header');
		#$this->area_set('footer');
		#$this->area_set('search');
		#$this->area_set('navmenu');
		#$this->area_set('xlinks');
		#$this->area_set('banner');
	}
	function make($content) {
		html::make($content);
		il_set('title',$content->get('title','NULL: Interlecto'));
	}
};

return new ilui_html($this);
?>
