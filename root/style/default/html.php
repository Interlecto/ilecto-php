<?php ob_start()?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{page:title}</title>
<link rel="stylesheet" href="{style}css/style.css" type="text/css" />
<link rel="stylesheet" href="{style}css/layout.css" type="text/css" />
<link rel="icon" href="{page:site:icon}">
</head><!-- {page:type} -->
<body class="{page:class}">
  {area:header}

  <div class="content" id="body">
    <h1 id="title">{page:title}</h1>
    {content}
  </div>

  <div class="aside" id="asides">
    <h2 class="o">Enlaces</h2>
    {area:search}

    {area:navmenu}

    {area:xlinks}
  </div>

  {area:footer}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
</body>
</html>
<?php $GLOBALS['HTML_Template'] = ob_get_clean();

require_once 'lib/html.php';
class ilui_html extends html {
	public $style = '/style/default/';
	function __construct($page) {
		html::__construct($page);
		il_set('site/motto','Tu interlocución y presencia en nuevos paradigmas sociales',SET_EMPTY);
		header('Content-type: text/html;charset=utf-8');
		$this->area_set('header');
		$this->area_set('footer');
		$this->area_set('search');
		$this->area_set('navmenu');
		$this->area_set('xlinks');
		$this->area_set('banner');
	}
	function make($content) {
		html::make($content);
		il_set('title',$content->get('title','NULL: Interlecto'));
	}
};

return new ilui_html($this);
?>
