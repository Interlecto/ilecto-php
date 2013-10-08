<?php
ob_start();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>{page:title}</title>
	<link rel="stylesheet" href="{style}css/style.css" type="text/css" />
  </head>
  <body>
    <div class="header" id="header">
	  <div class="logo"><a href="/"><b>{page:site:name}</b></a></div>
	  <p class="motto">{page:site:motto}.</p>
	  <h1>{doc:title}</h1>
      <div class="nav" id="mainnav">
        <ul>
          <li><a href="/nosotros">¿Quiénes somos?</a></a></li>
          <li><a href="/servicios">Nuestros servicios</a></a></li>
          <li><a href="/proyectos">Nuestros proyectos</a></a></li>
          <li><a href="/feedback">Contáctenos</a></a></li>
        </ul>
      </div>
    </div>
    <div class="wrap" id="body">
      <div class="article" id="content">
		  {content}
      </div>
      <div class="aside" id="left-sb">
        <div class="section widget">
          <h3>Enlaces</h3>
          {menu:enlaces}
          <ul>
            <li><a href="http://gallery.interlecto.net/">Galer&iacute;a de Interlecto</a></li>
            <li><a href="http://chlewey.net/">Chlewey.net</a></li>
            <!--li><a href="http://www.planearte.com/">Planearte</a></li-->
            <!--li><a href="http://rese.chlewey.net/">Rese Ltda</a></li-->
            <li><a href="http://pp.interlecto.net/">Partido Pirtata Colombiano</a></li>
            <!--li><a href="http://wmc.interlecto.net/">Wikimedia Colombia</a></li-->
            <li><a href="http://www.pediatria.org.co/">Sociedad Colombiana de Pediatr&iacute;a &ndash; Regional Bogot&aacute;</a></li>
            <li><a href="http://www.orugaamarilla.com/">Oruga Amarilla</a></li>
            <li><a href="http://fing.javeriana.edu.co/simposio/">Simposio Electr&oacute;nica &ndash; Universidad Javeriana 2010</a></li>
          </ul>
        </div>
        <div class="section widget" id="adsense-links">
          {area:adsense}
        </div>
      </div>
      <div class="cleanup"></div>
    </div>
    {area:footer}
    <div class="aside" id="feedback">
      <a href="/feedback"><b>feedback</b></a>
    </div>
  </body>
</html><?php $GLOBALS['HTML_Template'] = ob_get_clean();

require_once 'lib/html.php';
class ilecto_html extends html {
	public $style = '/style/ilecto_old/';
	function __construct($page) {
		html::__construct($page);
		il_set('site/motto','Tu interlocución y presencia en nuevos paradigmas sociales',SET_EMPTY);
		header('Content-type: text/html;charset=utf-8');
		$this->area_set('footer');
		$this->area_set('banner');
		$this->area_set('adsense');
		$this->menu_set('enlaces');
	}
	function make($content) {
		html::make($content);
		il_set('title',$content->get('title','NULL: Interlecto'));
	}
};

return new ilecto_html($this);
?>
