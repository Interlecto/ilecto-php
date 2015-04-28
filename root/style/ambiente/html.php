<?php ob_start()?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{page:title}</title>
<link rel="stylesheet" href="{style}css/style.css" type="text/css" />
<link rel="stylesheet" href="{style}css/layout.css" type="text/css" />
<link rel="stylesheet" href="{style}css/content.css" type="text/css" />
<link rel="icon" href="{page:site:icon}">
</head><!-- {page:type} -->
<body class="{page:class}">
  {area:header}

  <div class=main id=main>
    <hgroup id=docheader>
      <h1 id=pagename>{page:title}</h1>
    </hgroup>
    {area:breadcrumbs}
    <article id=content>
      {content}
    </article>
  </div>
  {area:topbar}
  {area:sidebar}
  {area:footer}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
</body>
</html><pre>{page:queries}</pre>
<?php $GLOBALS['HTML_Template'] = ob_get_clean();

$dbx = db_select_one('base_content','record',array('key'=>'=@ambiente'));
if(empty($dbx) || il_exists('getv/install_ambiente'))
	require 'style/ambiente/install.php';
$dby = $dbx+15;
$GLOBALS['applets'] = db_select('base_content','key',array('record'=>"&{$dbx}&{$dby}"));
$GLOBALS['objects'] = db_select('base_object');

require_once 'lib/html.php';
class ambiente_html extends html {
	public $style = '/style/ambiente/';
	function __construct($page) {
		html::__construct($page);
		header('Content-type: text/html;charset=utf-8');
		global $applets,$objects;
		foreach($objects as $object) {
			$fn = $object['class'].'_set';
			$this->$fn($object['key']);
		}
		foreach($applets as $applet) {
			if(preg_match('{^@(a\w+):(\w[-\w]*)$}',$applet['key'],$m)) {
				$fn = $m[1].'_set';
				$this->$fn($m[2]);
			}
		}
	}
	function make($content) {
		html::make($content);
		il_set('title',$content->get('title','NULL: Interlecto'));
	}
};

return new ambiente_html($this);
?>
