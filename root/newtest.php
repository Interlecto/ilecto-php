<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ILM Test</title>
<link rel="stylesheet" href="/style/default/css/style.css" type="text/css" />
<link rel="stylesheet" href="/style/default/css/layout.css" type="text/css" />
<link rel="icon" href="/images/interlecto.ico">
<style></style>
</head>
<?php

$ilmt = <<<ILM
[b:body
	[b:head
		[nav:p.o {text:Go to} [!#search{text:search}], [!#menu{text:menu}], [!#links{text:links}]]
		[p:h "The Page"]
	]
	[b:content
		[p:h "The Doc Title"]
		[b:section
			[p:h Lorem Ipsum]
			[p: Lorem ipsum dolor sit amet, consectetur adipiscing elit.
				Donec lacinia consectetur nisi, id aliquam leo tristique non.
				Cras ut ullamcorper nunc, sit amet ultricies tellus.
				Nunc maximus, felis vel i vehicula pretium, diam ante faucibus sapien, et fermentum ipsum sem in dui.
				Mauris eu lorem vitae libero fermentum scelerisque.]
		]
		[b:section{@title:Phrases}
			[p:bullet ¡_46_e1ct _u00e0_20__00006ca c__101rte!]
			[p:bullet {text:The quick brown fox jumps over the lazy dog}.]
			[p:bullet Allá exigí una libra de azúcar, dos de kiwis y un poco de arroz, jamón y güeñas que no tenía, leche, huevos y café.{@lang:es}]
			[p:bullet{@lang:de}Zwölf Boxkämpfer jagten Victor quer über den großen Sylter Deich.]
			[p:bullet{@lang:ei}Kæmi ný öxi hér ykist þjófum nú bæði víl og ádrepa]
			[p:bullet{@lang:he:rtl}עטלף אבק נס דרך מזגן שהתפוצץ כי חם]
			[p:bullet{@lang:ei}Kuba harpisto ŝajnis amuziĝi facilege ĉe via ĵaŭda ĥoro.]
			[p:bullet{@lang:sv}Flygande bäckasiner söka hwila på mjuka tuvor.]
		]
		[b:section
			[p:h Lists]
			[b:section{@title:Simple lists}]
				[p: Simple lists use the p namespace _22"[p:bullet"_22]
				[p:bullet uno]
				[p:disc dos]
				[p:circle tres]
				[p:disc cuatro]
				[p::facebook cinco]
			[b:section{@title:Bulleted lists}]
				[p: Normal lists use the l namespace _22"[l:bullet"_22]
				[l:bullet uno]
				[l:disc dos]
				[l:circle tres]
				[l: cuatro]
			[b:section{@title:Icon lists}]
				[l::flickr uno]
				[l::instagram dos]
				[l::facebook tres]
			[b:section{@title:Ordered lists}]
				[l:# uno]
				[l:# dos]
				[l:alpha tres]
				[l:alpha cuatro]
			[b:section{@title:Structured lists}]
				[l:# uno
					[l:# uno.uno]
					[l:* uno.dos]
				]
		]
		[b:section{@title:Tables}
			[t:fancy.cf
				[t:head
					[t:row
						[t:h]
						{for:col:1:5}[t:d Column {:col}]{next}
					]
				]
				{for:sec:1:3}[t:body
					[t:row[t:h§{:sec}][t:h{@colspan:5}]]
					{for:row:1:sec+1}[t:row
						[t:h §{:sec} row-{alpha:row}]
						{for:col:1:row}[t:d §{:sec} {alpha:row}{:col}]{next}
						[t:d.grayed{@colspan:(5-row)} left]
					]{next}
				]{next}
			{@title:Table test}]
		]
	]
	[b:aside
		[f:#search]
		[nav:menu#menu]
		[nav:menu#links]
	]
	[b:foot
		{ui:copyleft} 2015, Interlecto
	]
]
ILM;

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
	if(isset(ILM::$nsdic[$ns][$tag]))
		return ILM::$nsdic[$ns][$tag];
	if(isset(ILM::$nsalias[$ns][$tag])) {
		$tag = ILM::$nsalias[$ns][$tag];
		if(($n=strpos($tag,'.'))!==false) $tag = substr($tag,0,$n);
		return ILM::$nsdic[$ns][$tag];
	}
	return null;
}
static function is_space($txt){return is_string($txt)?preg_match('{^\s+$}u',$txt):(is_array($txt)&&count($txt)?ILM::is_space($txt[0]):false);}
static function is_word($txt){return is_string($txt)?preg_match('{^\w+$}u',$txt):(is_array($txt)&&count($txt)?ILM::is_word($txt[0]):false);}
static function is_urable($txt){return is_string($txt)?preg_match('{^[^][{}\s]}u',$txt):(is_array($txt)&&count($txt)?ILM::is_urable($txt[0]):false);}
static function is_quote($txt){return is_string($txt)?preg_match('{^".}u',$txt):(is_array($txt)&&count($txt)?ILM::is_quote($txt[0]):false);}
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
				#echo "<!-- [$key] [{$toks[0]}][{$toks[1]}][{$toks[2]}][{$toks[3]}] -->\n";
				$ns = ILM::$nsstack[0];
				if(!is_null($class = ILM::get_class($key,$ns))) {
					#echo "<!-- ** [$key] [$ns] [$class] -->\n";
					return new $class(":$key",$toks);
				}
				if(!is_null($class = ILM::get_class($key,'')))
					return new $class("$key",$toks);
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
			$t->addclass($cl);
			ILM::ltrim($toks);
			return;
		case '#':
			if(empty(ILM::$tagstack) or ILM::$tagstack[0]->count() or !ILM::is_word($toks))
				return new ILMstr($tok,$toks);
			$t = ILM::$tagstack[0];
			$id = array_shift($toks);
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
	if(method_exists($obj,'text')) {
		$r = $obj->text();
	} elseif(is_array($obj)) {
		$r = '';
		foreach($obj as $item)
			$r.= ILM::totext($obj);
	} elseif(is_string($obj)) {
		$r = $obj;
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
		return $this->container[$n];
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
	function html() {
		return ILM::unescape($this->code);
	}
	function text() {
		return ILM::unescape($this->code);
	}
}

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
ILM::add_class('div.content',null,'b','content');

class ILMpar extends ILMtag {
	function __construct($code,array &$tokens) {
		ILMtag::__construct($code,$tokens);
		$tag = $this->code;
		$ns = empty($this->namespace)? 'p': $this->namespace;
		if(!isset(ILM::$nsdic[$ns][$tag]) && !isset(ILM::$nsalias[$ns][$tag])) {
			$dt = ILM::$nsalias[$ns][''];
			ILM::$nsalias[$ns][$tag] ="$dt.li.$tag";
			$this->code = $dt;
			$this->addclass("li $tag");
		}
	}
};
ILM::add_namespace('p','ILMpar');
#ILM::add_class('h2',null,'p','h');
#ILM::add_class('p.bullet',null,'p','bullet');
ILM::add_class('table.fancy',null,'t','fancy');
ILM::add_class('thead',null,'t','head');
ILM::add_class('tbody',null,'t','body');
ILM::add_class('tr',null,'t','row');
ILM::add_class('th',null,'t','h');
ILM::add_class('td',null,'t','d');

class ILMsection extends ILMtag {
	function __construct($code,array &$tokens) {
		if(isset(ILM::$flags['level'])) ++ILM::$flags['level'];
		else ILM::$flags['level']=2;
		ILMtag::__construct($code,$tokens);
		ILM::$flags['level']--;
	}
};

class ILMlinkin extends ILMtag {
	function htmlopen($version=5) {
		$anchor = isset($this->link)?
			htmltag('a',HTMLT_OPEN,array('href'=>$this->link),$version):
			'';
		return ILMtag::htmlopen().$anchor;
	}
	function htmlclose($version=5) {
		$canchor = isset($this->link)?
			htmltag('a',HTMLT_CLOSE):
			'';
		return $canchor.ILMtag::htmlclose();
	}
};
class ILMli extends ILMlinkin {
	function __construct($code,array &$tokens) {
		if(!is_a($t=ILM::$tagstack[0],'ILMlcont')) {
			$o = ILM::read('[l:ph]');
			$o->push($this);
			$t->push($o);
			$o->addkey('parent',$t);
		}
		ILMtag::__construct($code,$tokens);
		$tag = $this->code;
		$ns = empty($this->namespace)? 'l': $this->namespace;
		if(!isset(ILM::$nsdic[$ns][$tag]) && !isset(ILM::$nsalias[$ns][$tag])) {
			$dt = ILM::$nsalias[$ns][''];
			ILM::$nsalias[$ns][$tag] ="$dt.li.$tag";
			$this->code = $dt;
			$this->addclass("li $tag");
		}
		ILM::ltrim($tokens);
	}
};
class ILMlcont extends ILMtag {
	#function html($version=5) {
	#	echo '<!--'.print_r($this,true).'-->';
	#}
};
class ILMphcont extends ILMlcont {
	function html($version=5) {
		$t = $this->parent;
		#echo '<!--'.print_r($this,true).'-->';
		$n = $t->search($this)+1;
		while(is_a($o=$t->get($n),'ILMli') || is_a($o,'ILMphcont')) {
			if(is_a($o,'ILMli') && $o!=$this->get(0))
				$this->push($o);
			$t->pop($n);
		}
		return $db.ILMlcont::html($version);
	}
};
ILM::add_class('li','ILMli','l','l');
ILM::add_class('ol','ILMlcont','l','o');
ILM::add_class('ul','ILMlcont','l','u');
ILM::add_class('ul.ph','ILMphcont','l','ph');
ILM::add_class('dd','ILMli','l','d');
ILM::add_class('dt','ILMli','l','t');

class ILMheading extends ILMlinkin {
	function __construct($code,array &$tokens) {
		$level = isset(ILM::$flags['level'])? ILM::$flags['level']: 1;
		$tagname = "h$level";
		ILMlinkin::__construct($tagname,$tokens);
	}
};
ILM::add_class('section','ILMsection','b');
ILM::add_class('h','ILMheading','p');

class ILMlinkout extends ILMtag {
	function htmlopen($version=5) {
		$anchor = isset($this->link)?
			htmltag('a',HTMLT_OPEN,array('href'=>$this->link),$version):
			'';
		return $anchor.ILMtag::htmlopen();
	}
	function htmlclose($version=5) {
		$canchor = isset($this->link)?
			htmltag('a',HTMLT_CLOSE):
			'';
		return ILMtag::htmlclose().$canchor;
	}
};

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

class ILMmath {
static $vars = array();
static function set($var,$val) {ILMmath::$vars[$var] = $val;}
static function val($var,$def=0) {return isset(ILMmath::$vars[$var])? ILMmath::$vars[$var]: $def;}
static function inc($var,$step=1,$target=null) {if(isset(ILMmath::$vars[$var])) ILMmath::$vars[$var]+=$step; else ILMmath::$vars[$var]=$step;return ILMmath::$vars[$var]<=$target;}
static function dec($var,$step=1,$target=null) {if(isset(ILMmath::$vars[$var])) ILMmath::$vars[$var]-=$step; else ILMmath::$vars[$var]=-$step;return ILMmath::$vars[$var]>=$target;}
static function evaluate($str,$def=0) {
	if(is_a($str,'ILMmath')) return $str->solve($def);
	if(is_numeric($str))return $str;
	if(ILM::is_word($str))return ILMmath::val($str,$def);
	$o = ILMmath::make($str);
	return $o->solve($def);
}
static function make($str,$def=0) {
	preg_match_all(
		'{'.'\d+'.
		'|'.'\w+'.
		'|'.'/[;:]?'. # / is integer division with floor approximation, /; is integer division with closest int approximation, /: is integer division with ceiling approximation
		'|'.'\W'.
		'}u',
		$str, $matches);
	return new ILMmath($matches[0],$def);
}
	function __construct(array&$toks,$def=0) {
		if(empty($toks)) {
			$this->node = $def;
			return;
		}
		if(count($toks)==1) {
			$this->node = array_shift($toks);
			return;
		}
		$c = array();
		$d = array();
		$o = '';
		while(!empty($toks)) {
			$k = array_shift($toks);
			if(empty($o)) {
				if($k=='+' or $k=='-') $o = $k;
				else
					$c[] = $k=='('? new ILMpared($toks): $k;
			} else {
				$d[] = $k=='('? new ILMpared($toks): $k;
			}
		}
		#print_r(array($c,$o,$d));
		if(count($c)==1 && empty($o)) {
			$this->node = $c[0];
			return;
		}
		if(!empty($o)) {
			$this->oper = $o;
			$this->pre = new ILMmath($c,$def);
			$this->pos = new ILMmath($d,0);
			return;
		}
		while(!empty($c)) {
			$k = array_shift($c);
			if(in_array($k,array('*','/','/;','/:','%'))) {
				$o = $k;
				break;
			}
			$d[] = $k;
		}
		if(!empty($o)) {
			$this->oper = $o;
			$this->pre = new ILMmath($d,$def);
			$this->pos = new ILMmath($c,1);
			return;
		}
		$this->node = $def;
	}
	function solve($def=0) {
		if(isset($this->node)) {
			if(is_a($this->node,'ILMmath')) return $this->node->solve($def);
			return is_numeric($this->node)? $this->node: ILMmath::val($this->node,$def);
		}
		if(isset($this->oper)) {
			$pre = $this->pre->solve();
			$pos = $this->pos->solve();
			switch($this->oper) {
			case '+':
				return $pre + $pos;
			case '-':
				return $pre - $pos;
			case '*':
				return $pre * $pos;
			case '%':
				return $pre % $pos;
			case '/':
				return floor($pre / $pos);
			case '/;':
				return round($pre / $pos);
			case '/:':
				return ceil($pre / $pos);
			}
		}
		return $def;
	}
	function __toString(){return (string)($this->solve());}
};
class ILMpared extends ILMmath {
	function __construct(array&$toks,$def=0) {
		$c = array();
		while(!empty($toks) && ($k=array_shift($toks))!=')') {
			if($k=='(')
				$c[] = new ILMpared($toks);
			else
				$c[] = $k;
		}
		$this->node = new ILMmath($c);
	}
};

class ILMbrace extends ILM {
static $classes = array();
static $actions = array();
static function add_class($key,$class){ILMbrace::$classes[$key]=$class;}
static function add_action($key,$class){ILMbrace::$actions[$key]=$class;}
static function is_end($item) { return is_a($item,'ILMdone') or is_a($item,'ILM_else'); }
static function new_brace(array &$tokens) {
	if($tokens[0]=='@') {
		$key = $tokens[1];
		if(isset(ILMbrace::$actions[$key])) $cl=ILMbrace::$actions[$key];
		elseif(class_exists($cl="ILM$key") && is_a($cl,'ILMaction',true));
		else $cl = 'ILMaction';
		return new $cl($tokens);
	}
	$key = $tokens[0];
	if(isset(ILMbrace::$classes[$key])) $cl=ILMbrace::$classes[$key];
	elseif(class_exists($cl="ILM$key") && is_a($cl,'ILMbrace',true));
	else $cl = 'ILMbrace';
	return new $cl($tokens);
	#$obj = new $cl($tokens);
	#echo '<!--'.print_r($obj,true).'-->';
	return $obj;
}
	function __construct(array &$tokens) {
		$s = '';
		$a = array();
		while($tokens and ($t=array_shift($tokens))!='}') {
			#echo "$t -- $s\n";
			if($t==':') {
				$a[] = $s;
				$s = '';
			} else
				$s.= $t;
		}
		$a[] = $s;
		for($i=0;$i<count($a);++$i)
			if(substr($a[$i],0,1)=='(') $a[$i] = ILMmath::make($a[$i]);
		$this->code = array_shift($a);
		$this->parts = $a;
	}
};

class ILMtext extends ILMbrace {
	function html($version=5) { return $this->text(); }
	function text() {
		$k = $this->parts;
		$f = array_pop($k);
		global $i18n;
		if(isset($i18n[$f])) return $i18n[$f];
		return $f;
	}
	/*function __construct(array &$tokens) {
		ILMbrace::__construct($tokens);
		echo '<!--'.print_r($this,true).'-->';
	}/**/
};
ILMbrace::add_class('text','ILMtext');

class ILMui extends ILMtext {
		function html() {
			$cl = "ui";
			$k = $this->parts;
			$l = array_pop($k);
			if(($n=strpos($l,' '))!==false) {
				$cont = substr($l,$n+1);
				$l = substr($l,0,$n);
			}
			array_push($k);
			foreach($this->parts as $p);
				$cl.=" ui_$p";
			return htmltag('i',HTMLT_OPEN,array('class'=>$cl)).
				$cont.htmltag('i',HTMLT_CLOSE);
		}
}

class ILMvar extends ILMtext {
static function alpha($n) { return chr(64+$n); }
	function text() {
		$k = $this->parts;
		$var = array_pop($k);
		$val = ILMmath::evaluate($var);
		if(method_exists('ILMvar',$m=$this->code)) {
			return ILMvar::$m($val);
		}
		return $val;
	}
};
ILMbrace::add_class(':','ILMvar');
ILMbrace::add_class('alpha','ILMvar');

class ILMdone extends ILMbrace {
};
ILMbrace::add_class('next','ILMdone');
ILMbrace::add_class('fi','ILMdone');

class ILMblock extends ILMbrace {
	private $content;
	function __construct(array &$tokens) {
		$this->content = new ILMcontainer;
		ILMbrace::__construct($tokens);
		while(!empty($tokens)) {
			$tok = array_shift($tokens);
			$o = ILM::readone($tok,$tokens);
			if(ILMbrace::is_end($o)) break;
			$this->content->push($o);
		}
		if(is_a($o,'ILMelse')) {
			$this->close = $o;
		}
		$this->count = 1;
	}
	
	function condition() {
		return 0<$this->count--;
	}
	function if_next() {
		return false;
	}
	
	function html($version=5) {
		$s = '';
		while($this->condition())
			$s.= $this->content->html($version);
		if(isset($this->close) && if_next())
			$s.= $this->close->html($version);
		return $s;
	}
	
	function text() {
		$s = '';
		while($this->condition())
			$s.= $this->content->text();
		if(isset($this->close) && if_next())
			$s.= $this->close->text();
		return $s;
	}
};
class ILMfor extends ILMblock {
	function __construct(array &$tokens) {
		ILMblock::__construct($tokens);
		$this->var = array_shift($this->parts);
		$this->ini = array_shift($this->parts);
		$this->end = array_pop($this->parts);
		$this->step = empty($this->parts)? 1: $this->parts[0];
	}
	function condition() {
		if(!isset($this->curr))
			$this->curr = ILMmath::evaluate($this->ini);
		ILMmath::set($this->var,$this->curr);
		$curr = $this->curr;
		$step = ILMmath::evaluate($this->step);
		$end  = ILMmath::evaluate($this->end);
		$r = ($curr*$step)<=($end*$step);
		$this->curr += $step;
		return $r;
	}
	function html($version=5) {
		$r = ILMblock::html($version);
		unset($this->curr);
		return $r;
	}
}
ILMbrace::add_class('if','ILMblock');
#ILMbrace::add_class('for','ILMfor');

class ILMelse extends ILMblock {
};
#ILMbrace::add_class('else','ILMelse');
ILMbrace::add_class('elif','ILMelse');

class ILMaction extends ILMbrace {
	function __construct(array &$tokens) {
		array_shift($tokens);
		ILMbrace::__construct($tokens);
		$tag = $this->tag();
		$this->apply($tag);
	}
	function tag() {
		return ILM::$tagstack[0];
	}
	function apply($tag) {
		$tag->addattrib($this->code,count($this->parts==1)? $this->parts[0]: implode(':',$this->parts));
	}
	function html() { return ''; }
	function text() { return ''; }
}

class ILMtitle extends ILMaction {
	function apply($tag) {
		$first = true;
		$k = $this->parts;
		$f = array_pop($k);
		switch($tag->code) {
		case 'frameset':
			$c = 'legend';
			break;
		case 'table':
			$c = 'caption';
			$first = false;
			break;
		default:
			$c = 'p:h';
		}
		array_unshift($k,$c);
		$id = strtolower($f);
		$id = str_replace(' ','_',$id);
		$o = ILM::read('['.implode('.',$k).'#'.$id.'"'.$f.'"]');
		$tag->addchild($o,$first);
	}
}
#ILMbrace::add_action('title','ILMtitle');

#echo '<!--'.print_r(ILMbrace::$classes,true).'-->';

$i18n = array(
'Go to'=>'Ir a',
'search'=>'búsqueda',
'menu'=>'menú',
'links'=>'enlaces',
);
include_once 'lib/tidy.php';
$ob = ILM::read($ilmt);
echo ILM::tohtml($ob,null,true);
#echo ILM::totext($ob);
#print_r(ILM::$nsdic);
#print_r(ILM::$nsalias);

?>

</html>
