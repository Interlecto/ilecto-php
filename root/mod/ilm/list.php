<?php
require_once 'tag.php';

class ILMli extends ILMlinkin {
	function __construct($code,array &$tokens) {
		if($tokens[0]=='#' && !ILM::is_word($tokens[1])) {
			$style = 'o';
			array_shift($tokens);
		} elseif ($tokens[0]=='*') {
			$style = 'u';
			array_shift($tokens);
		} else {
			$style = 0;
		}
		/*if(!is_a($t=ILM::$tagstack[0],'ILMlcont')) {
			$o = ILM::read('[l:'.$this->style.']');
			$o->push($this);
			$t->push($o);
			$o->addkey('parent',$t);
			ILMmath::inc('@li');
			$i = ILMmath::val('@li');
			$o->addkey('sign',$i);
			$o->addkey('first',true);
			$o->addkey('last',true);
		}*/
		$p = ILM::$tagstack[0];
		$n = $p->count();

		ILMtag::__construct($code,$tokens);

		$tag = $this->code;
		if($tag=='l') $this->code = 'li';
		$ns = empty($this->namespace)? 'l': $this->namespace;
		if(!isset(ILM::$nsdic[$ns][$tag]) && !isset(ILM::$nsalias[$ns][$tag])) {
			$dt = ILM::$nsalias[$ns][''];
			ILM::$nsalias[$ns][$tag] ="$dt.li.$tag";
			$this->code = $dt;
			$this->addclass("li $tag");
		}

		$this->style = $style;
		$this->first = $this->last = true;
		if($n) {
			$l = $p->get(-1);
			if(is_a($l,'ILMli')) {
				$this->first = false;
				$l->last = false;
				$this->style = $l->style;
			}
		}

		ILM::ltrim($tokens);
	}
	function htmlopen($version=5) {
		$ct = $this->style.'l';
		if($ct!='ol') $ct='ul';
		$cont = $this->first?
			htmltag($ct,HTMLT_OPEN,null,$version):
			'';
		return $cont.ILMlinkin::htmlopen($version);
	}
	function htmlclose($version=5) {
		$ct = $this->style.'l';
		if($ct!='ol') $ct='ul';
		$cont = $this->last?
			htmltag($ct,HTMLT_CLOSE,null,$version):
			'';
		return ILMlinkin::htmlclose($version).$cont;
	}
};
/*
class ILMlcont extends ILMtag {
	#function __construct($code,array &$tokens) {
	#	ILMtag::__construct($code,$tokens);
	#}
	function html($version=5) {
		$s = 'A';
		$x = false;
		if(isset($this->parent)) {
			$t = $this->parent;
			for($i=0;$i<$t->count();$i++) {
				$o = $t->get($i);
				$s.= ", $i ".get_class($o);
				if($x) {
					if(isset($o->write)) {
						$s.='=';
					}
					elseif(isset($o->first)) {
						$o->first = false;
						$o->code = $this->code;
						$u->last = false;
						$u = $o;
						$s.='-';
					}
					else
						$x = false;
					$s.= "/{$o->first},{$o->last},{$o->write}/";
				}
				if(isset($o->sign) && $o->sign==$this->sign) {
					$s.= '!me';
					$x = true;
					$u = $o;
				}
			}
		}
		$db = '';//"<!--({$this->sign})[$s]\n".print_r($this,true)."-->";
		return $this->write!==false? $db.ILMtag::html($version): $db;
	}
	function htmlopen($version=5) {
		return $this->first? ILMtag::htmlopen($version): '';
	}
	function htmlclose($version=5) {
		if(isset($this->parent)) {
			$t = $this->parent;
			for($i=0;$i<$t->count();$i++) {
				$o = $t->get($i);
				$s.= ", $i ".get_class($o);
				if(isset($o->sign) && $o->sign==$this->sign) {
					$u = $t->get($i+1);
					if(isset($u->write)) $u->write = false;
				}
			}
		}
		return $this->last? ILMtag::htmlclose($version): '';
	}
};/**/

ILM::add_class('li','ILMli','l','l');
ILM::add_class('ol','ILMlcont','l','o');
ILM::add_class('ul','ILMlcont','l','u');
ILM::add_class('ul.ph','ILMlcont','l','ph');
ILM::add_class('dd','ILMli','l','d');
ILM::add_class('dt','ILMli','l','t');

?>
