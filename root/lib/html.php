<?php
require_once "lib/output.php";

class html extends il_output {
	static $first;
	public $template;
	public $areas=array();
	public $menus=array();
	public $applets=array();

	function __construct($page) {
		if(!isset(html::$first)) html::$first = $this;
		il_output::__construct($page);
		$this->template = isset($GLOBALS['HTML_Template'])? $GLOBALS['HTML_Template']: null;
	}

	function make($content) {
		if(empty($this->template)) return il_output::make($content);
		$this->on_html($content);
		il_set('title',$content->get('title'),SET_UNSET);
		il_set('type',$content->get('type'),SET_UNSET);
		il_set('class',$content->get('class','common'),SET_UNSET);
		echo $this->make_template();#."<pre>html object: {\n".htmlentities($this->print_r("\t",true))."\n}</pre>";
	}

	function make_template() {
		$output = preg_replace_callback(
			'#\{(\w+)(:?)([^{}]*)\}#',
			'il_html_unbrace', $this->template);
		while(strpos($output,chr(27))!==false)
			$output = preg_replace('{\x1b[^\x1a\x1b]*\x1a}','',$output);
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

	function applet_set($name,$how=SET_REPLACE) {
		if(!isset($this->applets[$name])) $this->applets[$name] = new Applet($name);
	}

	function applet_get($name,$def=null,$how=DEF_UNSET) {
		if(isset($this->applets[$name])) return $this->applets[$name]->html();
		return $def;
	}

	function applet_add($name,$how=ADD_ARRAY) {
		if(!isset($this->applets[$name])) $this->applets[$name] = new Applet($name);
	}
};

function il_html_unbrace($m) {
	static $stack = array();
	$o = '';
	$p = false;
	$q = chr(27);
	$r = chr(26);
	if(count($m)>2 && $m[2]==':') {
		$k = str_replace(':','/',$m[3]);
		switch($m[1]) {
		case 'page':
			return il_get($k,$m[0]);
		case 'doc':
			return Doc::$first->get($k,$m[0]);
		case 'elif':
			$o = array_pop($stack);
			$p = substr($o,0,1)==$q;
		case 'if':
			$w = substr($k,0,4)=='doc/'? $Doc->$first->get($kk=substr($k,4),false):
				(substr($k,0,5)=='page/' ? il_get($kk=substr($k,5),false):
					il_get($kk=$k,false));
			$u = $q.($w? "<$kk>".($p?'':$r): "<!--[$kk]");
			$v = ($w? ($p?'':$q)."</$kk>": "[/$kk]-->").$r;
			array_push($stack,$v);
			return $o.$u;
		case 'text':
			if(isset($i18n[$m[3]])) return $i18n[$m[3]];
			return $m[3];
		default:
			$k = $m[1].'/'.$k;
			if($ar = il_get($k,false)) return $ar;
			if($ar = Doc::$first->get($k,false)) return $ar;
			return html::$first->get($k,$m[0]);
		}
	} else {
		switch($k=$m[1]) {
		case 'else':
		case 'fi':
			$o = array_pop($stack);
			if($m[1]=='else') {
				$u = "\x02<else>";
				array_push($stack,"</else>\x03");
			} else {
				$u = '';
			}
			return $o.$u;
		default:
			return html::$first->get($k,$m[0]);
		}
	}
	return '<code>.('.implode(')(',$m).')</code>';
	return $m[0];
}

require_once "lib/doc.php";
class Area extends Doc {
	public $name,$clss;
	function __construct($name) {
		Doc::__construct(Page::$first);
		$this->name = $name;
		$this->clss = 'area';
	}

	function make() {
		$this->make_from_db() or
		$this->make_from_file() or
		Doc::make();
	}
	function make_from_db() {
		$name = $this->name;
		$clss = $this->clss;
		if($r=db_select_first('base_object',null,array('key'=>"=$name"))) {
			if(($type = $r['type'])=='file') return 0;
			$this->set('type',$type);
			$this->set_content($r['record']);
			return 1;
		} 
		if($r=db_select_first('base_content',null,array('key'=>"=@$clss:$name"))) {
			if($r['as']=='file') return 0;
			$this->set('type',$r['type']);
			$this->set_content($r['record']);
			if($name=='header') var_dump($r);
			return 1;
		}
		return 0;
	}
	function make_from_file() {
		$name = $this->name;
		$style = il_get('style');
		if(file_exists($fn = "style/$style/$name.ilm")) {
			$this->set('type','ilm');
			$this->set('content',file_get_contents($fn));
			return 1;
		}
		if(file_exists($fn = "style/$style/$name.html")) {
			$this->set('type','html');
			$this->set('content',file_get_contents($fn));
			return 1;
		}
		if(file_exists($fn = "style/$style/$name.txt")) {
			$this->set('type','text');
			$this->set('content',file_get_contents($fn));
			return 1;
		}
		if(file_exists($fn = "style/$style/$name.php")) {
			$this->content = require $fn;
			return 1;
		}
		return 0;
	}
};

class Menu extends Area {
	function __construct($name) {
		Doc::__construct(Area::$first);
		$this->clss = 'menu';
	}
};

class Applet extends Area {
	function __construct($name) {
		Doc::__construct(Area::$first);
		$this->clss = 'applet';
	}
};

?>
