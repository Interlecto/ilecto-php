<?php
require_once 'ilm.php';

define('HTMLT_UNCLOSED',0);
define('HTMLT_OPEN',1);
define('HTMLT_CLOSE',2);
define('HTMLT_STANDALONE',3);
function htmltag($tag, $flags=HTMLT_OPEN, array $attribs=null, $version=5) {
	$x = strpos('-xX',substr($version,0,1));
	$o = is_numeric($version) && $version<=3;
	if($o) $tag=strtoupper($tag);
	if($flags===HTMLT_CLOSE) {
		return "</$tag>";
	}
	$r = "<$tag";
	if(!is_null($attribs))
		foreach($attribs as $k=>$v) {
			if($o) $k=strtoupper($k);
			if(!$v) continue;
			$r.=" $k=";
			if($v===True)
				$r.= $k;
			elseif(!$x && preg_match('{^[_\w][-_\w]*$}', $v))
				$r.= $v;
			else
				$r.= '"'.htmlentities($v).'"';
		}
	if($x && $flags==HTMLT_STANDALONE) $r.=" />";
	elseif($flags!=HTMLT_UNCLOSED) $r.=">";
	return $r;
}
class ILMtag extends ILMcontainer {
	private $attribs = array();
	function __construct($code,array &$tokens) {
		$k = explode(':',$code);
		$tag = array_pop($k);
		$ns = empty($k)? '': $k[0];
		if(isset(ILM::$nsalias[$ns][$tag])) $tag = ILM::$nsalias[$ns][$tag];
		/*echo "debug\r";if($ns=='b' && $tag=='head') {
			print_r(ILM::$nsalias);
			print_r(ILM::$nsdic);
		}/**/
		if(strpos($tag,'.')) {
			$kt = explode('.',$tag);
			$tag = array_shift($kt);
		}
		ILM::__construct($tag,$tokens);
		if(!empty($ns)) $this->namespace = $ns;
		if(!empty($kt)) $this->attribs['class'] = implode(' ',$kt);
		if(ILM::is_space($tokens)) array_shift($tokens);
		array_unshift(ILM::$tagstack,$this);
		while(!empty($tokens) && ($tok=array_shift($tokens))!=']') {
			$obj = ILM::readone($tok,$tokens);
			if($obj) $this->push($obj);
		}
		array_shift(ILM::$tagstack);
	}
	function html($version=5) {
		$r = $this->htmlopen($version);
		$r.= ILMcontainer::html($version);
		$r.= $this->htmlclose($version);
		return $r;
	}
	function htmlopen($version=5) {
		#echo $this->code.' ==> '.print_r($this->attribs,tru);
		return htmltag($this->code,HTMLT_OPEN,$this->attribs,$version);
	}
	function htmlclose($version=5) {
		return htmltag($this->code,HTMLT_CLOSE,null,$version);
	}
	
	function addclass($class,$key='class') {
		if(!isset($this->attribs[$key]))
			$this->attribs[$key] = $class;
		else
			$this->attribs[$key].=" $class";
	}
	function addattrib($key,$value) {
		$this->attribs[$key] = $value;
	}
	function addkey($key,$value) {
		if(!isset($this->$key))
			$this->$key = $value;
	}
	function addlink($link) {
		$this->link = $link;
	}
	function addchild($obj,$first=true) {
		if($first)
			$this->unshift($obj);
		else
			$this->push($obj);
	}
};
ILM::add_namespace('b',null,'div');
ILM::add_class('header',null,'b','head');
ILM::add_class('footer',null,'b','foot');
ILM::add_class('div.content',null,'b','content');

class ILManonym extends ILMtag {
	function htmlopen($version=5) {
		if(isset($this->link)) {
			$this->code = 'a';
			$this->addattrib('href',$this->link);
		} else {
			$this->code = 'span';
		}
		return ILMtag::htmlopen();
	}
};

class ILMsection extends ILMtag {
	function __construct($code,array &$tokens) {
		if(isset(ILM::$flags['level'])) ++ILM::$flags['level'];
		else ILM::$flags['level']=2;
		ILMtag::__construct($code,$tokens);
		ILM::$flags['level']--;
	}
};
ILM::add_class('section','ILMsection','b');

?>
