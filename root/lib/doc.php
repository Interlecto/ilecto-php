<?php
require_once "lib/attributer.php";

class Doc extends Attributer {
	static $first;
	public $page;
	function __construct($page) {
		if(!isset(Doc::$first)) Doc::$first = $this;
		Attributer::__construct(true);
		$this->protect[] = 'page';
		$this->page = $page;
	}
	
	function make() {
		$this->set('type','text',SET_UNSET);
		$this->set('content',htmlspecialchars(print_r($this,true)),SET_EMPTY);
	}
	
	function html($version = 5) {
		$this->make();
		switch($t = $this->get('type')) {
		case 'html':
			return $this->get('content');
		case 'text':
			return "<pre>".$this->get('content')."</pre>\n";
		case 'ilm':
			return ilm2html($this->get('content'), $version);
		default:
			return "<div class=\"$t\">".$this->get('content')."</div>\n";
		}
	}
	
	function set_content($record,$default=null,$writedown=true) {
		if($record==0) {
			if($writedown && !empty($default)) $this->set('content',$default);
			return $default;
		}
		$lang = $this->get('lang',il_get('lang',il_get('line/lang','en')));
		$r = db_select_key('base_text','lang',array('idx'=>"=$record"));
		if(empty($r)) {
			$c = $default;
			$t = null;
		} elseif(isset($r[$lang])) {
			$c = $r[$lang]['content'];
			$t = $r[$lang]['title'];
		} elseif(isset($r['en'])) {
			$lang = 'en';
			$c = $r[$lang]['content'];
			$t = $r[$lang]['title'];
		} elseif(isset($r['es'])) {
			$lang = 'es';
			$c = $r[$lang]['content'];
			$t = $r[$lang]['title'];
		} else {
			$k = array_keys($r);
			$lang = $k[0];
			$c = $r[$lang]['content'];
			$t = $r[$lang]['title'];
		}
		if($writedown) {
			if(!empty($c)) $this->set('content',$c);
			if(!empty($t)) $this->set('title',$t,SET_EMPTY);
			$this->set('lang',$lang);
			il_set('lang',$lang);
		}
		return $c;
	}
};

class nulldoc extends Doc {
};

class doc_row extends nulldoc {
};
?>
