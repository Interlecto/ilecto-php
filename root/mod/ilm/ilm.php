<?php

/**********************************************
 * ILM (ilm.php)
 * */
require_once "lib/arrayutils.php";

class ILM {
static $nsdic = array(''=>array());
static $nsalias = array(''=>array());
static $nsstack = array('');
static $flags = array();
static $tagstack = array();
static function add_namespace($ns,$baseclass='ILMtag',$default=null) {
	if(is_null($baseclass)) $baseclass='ILMtag';
	if(is_null($default)) $default=$ns;
	array_set(ILM::$nsdic,$ns,array($default=>$baseclass),ASET_UNSET);
	array_set(ILM::$nsalias,$ns,array(''=>$default),ASET_UNSET);
	array_set(ILM::$flags,$ns,array(),ASET_UNSET);
}
static function add_class($tag,$class='ILMtag',$ns='',$alias=null) {
	if(is_null($class)) $class='ILMtag';
	if(is_null($ns)) $ns='';
	if(!isset(ILM::$nsdic[$ns])) ILM::add_namespace($ns,$class,$tag);
	else ILM::$nsdic[$ns][$tag] = $class;
	if(!is_null($alias)) ILM::$nsalias[$ns][$alias] = $tag;
}
static function get_class($tag,$ns='') {
	if(empty($tag)) $tag='';
	if(isset(ILM::$nsalias[$ns][$tag])) {
		$tag = ILM::$nsalias[$ns][$tag];
		if(($n=strpos($tag,'.'))!==false) $tag = substr($tag,0,$n);
		return isset(ILM::$nsdic[$ns][$tag])? ILM::$nsdic[$ns][$tag]: 'ILMtag';
	}
	if(isset(ILM::$nsdic[$ns][$tag]))
		return ILM::$nsdic[$ns][$tag];
	return null;
}
static function is_space($txt){return is_string($txt)?preg_match('{^\s+$}u',$txt):(is_array($txt)&&count($txt)?ILM::is_space($txt[0]):false);}
static function is_word($txt){return is_string($txt)?preg_match('{^\w+$}u',$txt):(is_array($txt)&&count($txt)?ILM::is_word($txt[0]):false);}
static function is_urable($txt){return is_string($txt)?preg_match('{^[^][{}\s]}u',$txt):(is_array($txt)&&count($txt)?ILM::is_urable($txt[0]):false);}
static function is_quote($txt){return is_string($txt)?preg_match('{^".}u',$txt):(is_array($txt)&&count($txt)?ILM::is_quote($txt[0]):false);}
static function is_qnw($txt){return is_string($txt)?preg_match('{^"?\w+}u',$txt):(is_array($txt)&&count($txt)?ILM::is_quote($txt[0]):false);}
static function escape($text,$chars='[\]_{}') {
	$p = preg_quote($chars,'#');
	return preg_replace_callback("{[$p]}u",
		function($m){
			return sprintf('_%02X',ord($m[0]));
		},$text);
}
static function unescape($text) {
	$text = preg_replace('{\n\s*}',"\n",$text);
	$text = preg_replace('{\s+}',' ',$text);
	$text = preg_replace_callback(
		'{(__([0-9A-Fa-f]{1,6})|_[Uu]([0-9A-Fa-f]{4})|_([0-9A-Fa-f]{2}))}',
		function ($m) {
			$a = ltrim(array_pop($m),0);
			return mb_convert_encoding("&#x$a;", 'UTF-8', 'HTML-ENTITIES');
		},
		$text);
	return $text;
}
static function read($ilmtext) {
	preg_match_all(
		'{'.'\s*\n\s*'.
		'|'.'\s+'.
		'|'.'"[^"\n]*"'.
		'|'.'_([0-9A-Fa-f]{2}|[Uu][0-9A-Fa-f]{4}|_[0-9A-Fa-f]{1,6})'.
		'|'.'\d+'.
		'|'.'\w+'.
		'|'.'::'.
		'|'.'\W'.
		'}u',
		$ilmtext,
		$matches);
	$toks = $matches[0];
	$C = new ILMcontainer('null',$toks);
	while(!empty($toks)) {
		$tok = array_shift($toks);
		$C->push(ILM::readone($tok,$toks));
	}
	return $C->count()==1? $C->shift(): $C;
}
static function readone($tok,array &$toks) {
	if(strlen($tok)==1) {
		switch($tok) {
		case '[':
			if(!ILM::is_word($toks))
				return new ILManonym('',$toks);
			$key = array_shift($toks);
			if($toks[0]==':') {
				#echo "<!-- [$key] [{$toks[0]}][{$toks[1]}][{$toks[2]}][{$toks[3]}] -->\n";
				array_shift($toks);
				$ns = $key;
				$key = ILM::is_word($toks)? array_shift($toks): $ns;
				array_unshift(ILM::$nsstack,$ns);
				if(!is_null($class = ILM::get_class($key,$ns)))
					return new $class("$ns:$key",$toks);
				if(!is_null($class = ILM::get_class('',$ns)))
					return new $class("$ns:$key",$toks);
			} elseif(!empty(ILM::$nsstack)) {
				#echo "<!-- [$key] [{$toks[0]}][{$toks[1]}][{$toks[2]}][{$toks[3]}] (".ILM::$nsstack[0].") -->\n";
				$ns = ILM::$nsstack[0];
				if(!is_null($class = ILM::get_class($key,$ns))) {
					#echo "<!-- ** [$key] [$ns] [$class] -->\n";
					return new $class(":$key",$toks);
				}
				if(!is_null($class = ILM::get_class($key,$key))) {
					#echo "<!-- ** [$key] [$key] [$class] -->\n";
					return new $class("$key",$toks);
				}
				if(!is_null($class = ILM::get_class($key,''))) {
					#echo "<!-- ** [$key] [] [$class] -->\n";
					return new $class("$key",$toks);
				}
			} else {
				if(!is_null($class = ILM::get_class('',$key)))
					return new $class("$key:",$toks);
				if(!is_null($class = ILM::get_class($key,'')))
					return new $class("$key",$toks);
			}
			return new ILMtag("$ns:$key",$toks);
		case '{':
			return ILMbrace::new_brace($toks);
		case '.':
			if(empty(ILM::$tagstack) or ILM::$tagstack[0]->count() or !ILM::is_word($toks))
				return new ILMstr($tok,$toks);
			$t = ILM::$tagstack[0];
			$cl = array_shift($toks);
			while(!empty($toks) && in_array($toks[0],array('-','_')) && ILM::is_word($toks[1])) {
				$cl.= array_shift($toks);
				$cl.= array_shift($toks);
			}
			$t->addclass($cl);
			ILM::ltrim($toks);
			return;
		case '#':
			if(empty(ILM::$tagstack) or ILM::$tagstack[0]->count() or !ILM::is_word($toks))
				return new ILMstr($tok,$toks);
			$t = ILM::$tagstack[0];
			$id = array_shift($toks);
			while(!empty($toks) && in_array($toks[0],array('-','_')) && ILM::is_word($toks[1])) {
				$id.= array_shift($toks);
				$id.= array_shift($toks);
			}
			if(!empty($toks) && $toks[0]=='.' && ILM::is_word($toks[1])) {
				array_shift($toks);
				$v = array_shift($toks);
				$t->addattrib('name',$id);
				$t->addattrib('value',$v);
				$id.= "_$v";
			}
			if(!empty($toks) && $toks[0]=='@' && ILM::is_word($toks[1])) {
				array_shift($toks);
				$n = array_shift($toks);
				$t->addattrib('name',$n);
				$t->addattrib('value',$id);
			}
			$t->addattrib('id',$id);
			ILM::ltrim($toks);
			return;
		case '!':
			if(empty(ILM::$tagstack) or ILM::$tagstack[0]->count())
				return new ILMstr($tok,$toks);
			$t = ILM::$tagstack[0];
			if(ILM::is_quote($tok)) {
				$uri = trim(array_shift($tok),'"');
				$t->addlink($uri);
				return;
			}
			$uri = '';
			while(ILM::is_urable($toks))
				$uri.=array_shift($toks);
			if(empty($uri))
				return new ILMstr($tok,$toks);
			$t->addlink($uri);
			ILM::ltrim($toks);
			return;
		case '@':
			#echo "<!-- [{$toks[0]}][{$toks[1]}][{$toks[2]}][{$toks[3]}] -->\n";
			if(empty(ILM::$tagstack) or ILM::$tagstack[0]->count() or !ILM::is_word($toks))
				return new ILMstr($tok,$toks);
			$t = ILM::$tagstack[0];
			$v = array_shift($toks);
			#echo "<!-- [{$t->code}][{$v}] -->\n";
			if(ILM::is_quote($v)) $v = trim($v,'"');
			$t->addattrib('value',$v);
			ILM::ltrim($toks);
			return;
		default:
			return new ILMstr($tok,$toks);
		}
	} else {
		switch(substr($tok,0,1)) {
		case '"':
			return new ILMstr(trim($tok,'"'),$toks);
		case ':':
			if(empty(ILM::$tagstack) or ILM::$tagstack[0]->count() or !ILM::is_word($toks))
				return new ILMstr($tok,$toks);
			$t = ILM::$tagstack[0];
			$cl = array_shift($toks);
			$t->addclass('ui ui_'.$cl);
			return;
		default:
			return new ILMstr($tok,$toks);
		}
	}
}
static function ltrim(array &$toks) {
	while(ILM::is_space($toks)) array_shift($toks);
}
static function totext($ilmobject) {
	if(method_exists($ilmobject,'text')) {
		$r = $ilmobject->text();
	} elseif(is_array($ilmobject)) {
		$r = '';
		foreach($obj as $item)
			$r.= ILM::totext($obj);
	} elseif(is_string($ilmobject)) {
		$r = $ilmobject;
	}
	return $r;
}
static function tohtml($obj,$version=5,$tidied=false) {
	if(method_exists($obj,'html')) {
		$r = $obj->html($version);
	} elseif(is_array($obj)) {
		$r = '';
		foreach($obj as $item)
			$r.= ILM::tohtml($obj);
	} elseif(is_string($obj)) {
		$o = ILM::read($obj);
		$r = $o->html($version);
	}
	return $tidied? tidy::go($r,$version): $r;
}

	function __construct($code=null,array &$tokens=null) {
		$this->code = $code;
	}
	
	function html($version=5) {
		return '&lt;['.$this->code.']&gt;'.chr(10);
	}
	function text() {
		return $this->code;
	}
};

class ILMcontainer extends ILM {
	private $container = array();
	function pop($n) {
		if($n) {
			$item = $this->container[$n];
			$this->container = array_splice($this->container,$n,1);
			return $item;
		}
		return array_pop($this->container);
	}
	function push($item) {
		return array_push($this->container,$item);
	}
	function shift() {
		return array_shift($this->container);
	}
	function unshift($item) {
		return array_unshift($this->container,$item);
	}
	function search($item) {
		return array_search($this->container,$item);
	}
	function get($n) {
		return $n>=0? $this->container[$n]:
			$this->container[count($this->container)+$n];
	}
	function count() {
		return count($this->container);
	}
	function html($version=5) {
		$r = '';
		foreach($this->container as $item)
			$r.=ILM::tohtml($item,$version);
		return $r;
	}
	function text() {
		$r = '';
		foreach($this->container as $item)
			$r.=ILM::totext($item);
		return $r;
	}
}

class ILMstr extends ILM {
	function html($version=5) {
		return $this->text();
	}
	function text() {
		return ILM::unescape($this->code);
	}
}

require_once 'tag.php';
require_once 'par.php';
require_once 'list.php';
require_once 'nav.php';
require_once 'form.php';
require_once 'math.php';
require_once 'brace.php';
require_once 'control.php';
require_once 'action.php';
require_once 'image.php';

?>
