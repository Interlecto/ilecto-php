<?php
require_once "lib/attributer.php";

class Doc extends Attributer {
	static $first;
	public $page;
	function __construct($page) {
		$this->prepare_check();
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
			return ILM::tohtml($this->get('content'), $version);
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
		$s = db_select_key('base_titles','lang',array('idx'=>"=$record"));
		#print_r(array($r,$s));
		if(empty($r)) {
			$c = $default;
			$t = null;
		} elseif(isset($r[$lang])) {
			$c = $r[$lang]['content'];
			if(isset($s[$lang]))
				$t = $s[$lang]['title'];
		} elseif(isset($r['en'])) {
			$lang = 'en';
			$c = $r[$lang]['content'];
			if(isset($s[$lang]))
				$t = $s[$lang]['title'];
		} elseif(isset($r['es'])) {
			$lang = 'es';
			$c = $r[$lang]['content'];
			if(isset($s[$lang]))
				$t = $s[$lang]['title'];
		} else {
			$k = array_keys($r);
			$lang = $k[0];
			$c = $r[$lang]['content'];
			if(isset($s[$lang]))
				$t = $s[$lang]['title'];
		}
		if($writedown) {
			#print_r(array($c,$t));
			if(!empty($c)) $this->set('content',$c);
			if(!empty($t)) {
				$this->set('title',$t,SET_EMPTY);
				il_set('title',$t,SET_EMPTY);
				#print_r($this->title);
				#print_r($this->content);
				#print_r($this);
			}
			$this->set('lang',$lang);
			il_set('lang',$lang);
		}
		//print_r(Page::$first);
		return $c;
	}
	
	function dotlang($deflang='en') {
		$lang = il_get('line/lang',$deflang);
		return ($lang == $deflang)? '': ".$lang";
	}
	
	function prepare_check() {}
};

class nulldoc extends Doc {
};

class doc_row extends nulldoc {
};
?>
