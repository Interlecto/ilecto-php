<?php
require_once "lib/output.php";

class html extends il_output {
	static $first;
	public $template;
	public $areas=array();
	public $menus=array();
	
	function __construct($page) {
		if(!isset(html::$first)) html::$first = $this;
		il_output::__construct($page);
		$this->template = isset($GLOBALS['HTML_Template'])? $GLOBALS['HTML_Template']: null;
	}
	
	function make($content) {
		if(empty($this->template)) return il_output::make($content);
		$this->on_html($content);
		echo $this->make_template();
	}
	
	function make_template() {
		$output = preg_replace_callback(
			'#\{(\w+)(:?)([^{}]*)\}#',
			'il_html_unbrace', $this->template);
		return $output;
	}
	
	function area_set($name,$how=SET_REPLACE) {
		if(!isset($this->areas[$name])) $this->areas[$name] = new Area($name);
	}
	
	function area_get($name,$def=null,$how=DEF_UNSET) {
		if(isset($this->areas[$name])) return $this->areas[$name]->html();
		return $def;
	}
	
	function area_add($name,$how=ADD_ARRAY) {
		if(!isset($this->areas[$name])) $this->areas[$name] = new Area($name);
	}
	
	function menu_set($name,$how=SET_REPLACE) {
		if(!isset($this->menus[$name])) $this->menus[$name] = new Menu($name);
	}
	
	function menu_get($name,$def=null,$how=DEF_UNSET) {
		if(isset($this->menus[$name])) return $this->menus[$name]->html();
		return $def;
	}
	
	function menu_add($name,$how=ADD_ARRAY) {
		if(!isset($this->menus[$name])) $this->menus[$name] = new Menu($name);
	}
};

function il_html_unbrace($m) {
	if(count($m)>2 && $m[2]==':') {
		switch($m[1]) {
		case 'page':
		case 'site':
			$k = str_replace(':','/',$m[3]);
			return il_get($k,$m[0]);
		case 'doc':
			$k = str_replace(':','/',$m[3]);
			return Doc::$first->get($k,$m[0]);
		default:
			$k = $m[1].'/'.str_replace(':','/',$m[3]);
			if($ar = il_get($k,false)) return $ar;
			if($ar = Doc::$first->get($k,false)) return $ar;
			return html::$first->get($k,$m[0]);
		}
	} else {
		switch($k=$m[1]) {
		default:
			return html::$first->get($k,$m[0]);
		}
	}
	return '<code>.('.implode(')(',$m).')</code>';
	return $m[0];
}

require_once "lib/doc.php";
class Area extends Doc {
	public $name;
	function __construct($name) {
		Doc::__construct(Page::$first);
		$this->name = $name;
	}
	
	function make() {
		$name = $this->name;
		if($r=db_select_first('base_area',null,array('id'=>"=$name"))) {
			$this->set('type',$r['type']);
			$this->set_content($r['record']);
			return;
		}
		$style = il_get('style');
		if(file_exists($fn = "style/$style/$name.ilm")) {
			$this->set('type','ilm');
			$this->set('content',file_get_contents($fn));
			return;
		}
		if(file_exists($fn = "style/$style/$name.html")) {
			$this->set('type','html');
			$this->set('content',file_get_contents($fn));
			return;
		}
		if(file_exists($fn = "style/$style/$name.txt")) {
			$this->set('type','text');
			$this->set('content',file_get_contents($fn));
			return;
		}
		if(file_exists($fn = "style/$style/$name.php")) {
			$this->content = require $fn;
			return;
		}
		Doc::make();
	}
};

class Menu extends Area {
};

?>
