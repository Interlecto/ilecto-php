<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ILM Test</title>
<link rel="stylesheet" href="/style/default/css/style.css" type="text/css" />
<link rel="stylesheet" href="/style/default/css/layout.css" type="text/css" />
<link rel="icon" href="/images/interlecto.ico">
</head>
<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
$ilm = file_get_contents('mod/base/sample.ilm');

require_once "lib/arrayutils.php";

define('HTMLT_UNCLOSED',0);
define('HTMLT_OPEN',1);
define('HTMLT_CLOSE',2);
define('HTMLT_STANDALONE',3);
function htmltag($tag, $flags=HTMLT_OPEN, array $attribs=null) {
	if($flags===HTMLT_CLOSE) {
		return "</$tag>";
	}
	$r = "<$tag";
	if(!is_null($attribs))
		foreach($attribs as $k=>$v) {
			if(!$v) continue;
			$r.=" $k=";
			if($v===True)
				$r.= $k;
			elseif(preg_match('{^[_\w][-_\w]*$}', $v))
				$r.= $v;
			else
				$r.= '"'.htmlentities($v).'"';
		}
	if($flags!=HTMLT_UNCLOSED) $r.=">";
	return $r;
}

define('ILM_UNREFABLE',0);
define('ILM_SRC',1);
define('ILM_ANCHORIN',2);
define('ILM_ANCHOROUT',3);
class ILM {
static $tags = array();
static $alias = array();
static function add_tag($key,$class,$flags=ILM_UNREFABLE,$alias=null) {
	ILM::$tags[$key] = array($class,$flags);
	if($alias)
		ILM::$alias[$alias] = $key;
}
static function add_alias($alias,$refers) {
	ILM::$alias[$alias] = $refers;
}

static $braces = array();
static function add_brace($key,$class) {
	ILM::$braces[$key] = $class;
}

static $gflags = array();
static function set_flag($flag,$val=true) {
	ILM::$gflags[$flag] = $val;
}
static function unset_flag($flag) {
	unset(ILM::$gflags[$flag]);
}
static function is_flag($flag) {
	return isset(ILM::$gflags[$flag]) && ILM::$gflags[$flag];
}
static function get_flag($flag,$def=false) {
	return array_get(ILM::$gflags,$flag,$def);
}
static function flag_inc($flag,$min=0) {
	if(!isset(ILM::$gflags[$flag]) || ILM::$gflags[$flag]<$min)
		ILM::$gflags[$flag]=$min;
	else
		ILM::$gflags[$flag]++;
}
static function flag_dec($flag,$min=0) {
	if(!isset(ILM::$gflags[$flag]) || ILM::$gflags[$flag]<=$min)
		ILM::$gflags[$flag]=$min;
	else
		ILM::$gflags[$flag]--;
}

static function unscape($ilm) {
	$ilm = preg_replace('{\n\s*}',"\n",$ilm);
	$ilm = preg_replace('{\s+}',' ',$ilm);
	$ilm = preg_replace_callback(
		'{(__([0-9A-Fa-f]{1,6})|_[Uu]([0-9A-Fa-f]{4})|_([0-9A-Fa-f]{2}))}',
		function ($m) {
			$a = ltrim(array_pop($m),0);
			return mb_convert_encoding("&#x$a;", 'UTF-8', 'HTML-ENTITIES');
		},
		$ilm);
	return $ilm;
}

static function decode(&$text, $t='') {
	if(!$text) return false;
	if(preg_match('{^\s*(\])}',$text,$m)) {
		$text = substr($text,strlen($m[0]));
		if($t!='[')
			throw new Exception("Unmatched braces '$t' and '{$m[1]}'.\n");
		return false;
	} elseif(preg_match('{^\s*(\})}',$text,$m)) {
		$text = substr($text,strlen($m[0]));
		if($t!='{')
			throw new Exception("Unmatched braces '$t' and '{$m[1]}'.");
		return false;
	} elseif(preg_match('{^\s*\[(\w[-_\w]*(?:\:[-_\w]+)*|[^][{}\s\w#!.]+)?([^][{}\s]*)(\s*)}',$text,$m)) {
		$text = substr($text,strlen($m[0]));
		$tag = array_get(ILM::$alias,$m[1],$m[1]);
		if(($n=strpos($tag,'.'))!==false)
			$tag = substr($tag,0,$n);
		$class = array_get(ILM::$tags, $tag, array('ILM',ILM_UNREFABLE) );
		return new $class[0] ( $m, $text, $class[1] );
	} elseif(preg_match('{^\s*\{(\w*)((?:\:[-_./\w#]*)*)(\s*)([^{}]*)\}}',$text,$m)) {
		$text = substr($text,strlen($m[0]));
		$x = explode(':',$m[1]);
		$code = array_shift($x);
		$class = array_get(ILM::$braces, $code, 'ILMB' );
		return new $class ( $m, $text );
	} else {
		preg_match('{^(\s*[^][{}]*)}',$text,$m);
		if(strlen($m[0])==0 && strlen($text)!=0) {
			throw new Exception('Unmatched struct "'.substr($text,0,50).'".');
		}
		$text = substr($text,strlen($m[0]));
		return $m[1];
	}
}

static function tohtml(&$text) {
	$list = array();
	while(($item = ILM::decode($text))!==false) {
		$list[] = $item;
	}
	#print_r($list);
	$s = '';
	foreach($list as $item)
		#echo ILM::htmlitem($item);
		$s.= ILM::htmlitem($item);
	return $s;
}
static function htmlitem($item) {
	if(is_a($item,'ILM'))
		return $item->html();
	if(is_object($item) && method_exists($item,'html'))
		return $item->html();
	if(is_array($item)) {
		$r = '';
		foreach($item as $i)
			$r.= ILM::htmlitem($i);
		return $r;
	}
	return ILM::unscape($item);
}

	function openhtml($close=true) {
		if(empty($this->tag)) {
			$href = array_popkey($this->attribs,'href');
			if(ILM::is_flag('inform')) {
				$this->tag = 'button';
				$this->attribs['type'] = 'submit';
				if(!is_null($href)) {
					if($href=='!') {
						$this->attribs['type'] = 'reset';
					} else {
						$this->attribs['formaction'] = fixhref($href);
					}
				}
				if(count($this->content)==1 && is_string($this->content[0])) {
					$this->tag = 'input';
					$this->attribs['value'] = ILM::unscape(array_shift($this->content));
				} elseif(count($this->content)==0) {
					$this->tag = 'input';
				}
			} else {
				if(!is_null($href)) {
					$this->tag = 'a';
					$this->attribs['href'] = fixhref($href);
					if(empty($this->content))
						$this->content[] = $href;
				} else {
					$this->tag = 'span';
				}
			}
			return htmltag($this->tag,$close,$this->attribs);
		}
		if($this->flags==ILM_ANCHORIN || $this->flags==ILM_ANCHOROUT) {
			#$xx = preg_replace('{\s+}',' ',print_r($this->attribs,true));
			$href = array_popkey($this->attribs,'href');
			$htag = htmltag($this->tag,$close,$this->attribs);
			if(empty($href))
				return $htag;#."<!-- '$xx' -->";
			if(empty($this->content))
				$this->content[] = $href;
			$href = fixhref($href);
			$anchor = htmltag('a',HTMLT_OPEN,array('href'=>$href));
			$this->anchor = true;
			return $this->flags==ILM_ANCHORIN? $htag.$anchor: $anchor.$htag;
		}
		if($this->flags==ILM_SRC)
			$this->attribs['src'] = $href;
		elseif(is_string($this->flags))
			$this->attribs[$this->flags] = $href;
		return htmltag($this->tag,$close,$this->attribs);
	}

	function closehtml() {
		if($this->tag == 'input') return '';
		$anchor = empty($this->anchor)? '': htmltag('a', HTMLT_CLOSE);
		$tag = htmltag($this->tag, HTMLT_CLOSE);
		return $this->flags==ILM_ANCHOROUT? $tag.$anchor: $anchor.$tag;
	}
	function html() {
		$s = $this->openhtml();
		foreach($this->content as $item)
			$s.= ILM::htmlitem($item);
		$s.= $this->closehtml();
		return $s;
	}
/**/
	function setattribs($str) {
		preg_match_all('{([/_\w][-./_\w]*|::|\W)}', $str, $m);
		while($m[0]) {
			$a = array_shift($m[0]);
			switch($a) {
			case '.';
				$c = array_shift($m[0]);
				$c = str_replace('.',' ',$c);
				array_addclass($this->attribs,$c);
				break;
			case '#';
				$this->attribs['id'] = array_shift($m[0]);
				break;
			case '>';
				$f = array_shift($m[0]);
				$this->attribs['for'] = str_replace('.','_',$f);
				break;
			case '@';
				$this->attribs['value'] = array_shift($m[0]);
				break;
			case '+';
				$this->attribs['class'] = 'ui ui_'.array_shift($m[0]);
				break;
			case '!';
				$this->attribs['href'] = implode($m[0]);
				#echo "<!-- ".print_r($this,true).' -->';
				$m[0]=Null;
				break;
			case '::';
				$c = array_shift($m[0]);
				array_addclass($this->attribs,"ui ui_$c");
				break;
			default;
				$this->attribs[] = $a;
			}
		}
	}

	public function __construct(array $base, &$text, $flags=null) {
		$this->tag = $base[1];
		$this->attribs = array();
		$this->content = array();
		$this->flags = $flags;
		
		if(isset(ILM::$alias[$this->tag])) {
			$tagpair = ILM::$alias[$this->tag];
			if(($n=strpos($tagpair,'.'))) {
				$this->attribs['class'] = substr($tagpair,$n+1);
				$this->tag = substr($tagpair,0,$n);
			} elseif(($n=strpos($tagpair,'!'))) {
				$this->attribs['type'] = substr($tagpair,$n+1);
				$this->tag = substr($tagpair,0,$n);
			} else {
				$this->tag = $tagpair;
			}
		}
		if(isset(ILM::$tags[$this->tag]) && is_null($this->flags)) {
			$this->flags = ILM::$tags[$this->tag][1];
		}

		$mods = $base[2];
		$this->setattribs($mods);
		$this->esp = $base[3] != '';
		while(($x = ILM::decode($text,'['))!==false)
			$this->content[] = $x;
	}/**/
};

class ILMpar extends ILM {
static $level="";
	function openhtml($close=true) {
		ILMblock::$level.= "\t";
		return chr(10).ILMblock::$level.ILM::openhtml($close);
	}
	function closehtml() {
		ILMblock::$level = substr(ILMblock::$level,0,-1);
		return ILM::closehtml().chr(10);
	}
};
class ILMblock extends ILMpar {
static $level="";
	function closehtml() {
		return chr(10).ILMblock::$level.ILMpar::closehtml();
	}
};
ILM::add_tag('body','ILMblock');
ILM::add_tag('header','ILMblock');
ILM::add_tag('footer','ILMblock');
ILM::add_tag('p','ILMpar',ILM_ANCHORIN);
ILM::add_tag('legend','ILMpar');
ILM::add_tag('caption','ILMpar',ILM_ANCHORIN,'c');

ILM::add_tag('ul','ILMblock',ILM_UNREFABLE,'l');
ILM::add_tag('ol','ILMblock',ILM_UNREFABLE,'n');
ILM::add_tag('li','ILMblock',ILM_UNREFABLE,'o');

class ILMsection extends ILMblock {
	function openhtml($close=true) {
		ILM::flag_inc('level',2);
		return ILMblock::openhtml($close);
	}
	function closehtml() {
		ILM::flag_dec('level',2);
		return ILMblock::closehtml();
	}
};
ILM::add_tag('section','ILMsection',ILM_UNREFABLE,'§');
class ILMheading extends ILMpar {
	function openhtml($close=true) {
		$this->tag = 'h'.ILM::get_flag('level',1);
		return ILMpar::openhtml($close);
	}
};
ILM::add_tag('H','ILMheading',ILM_ANCHORIN);

ILM::add_tag('strong','ILM',ILM_ANCHOROUT,'b');
ILM::add_tag('em','ILM',ILM_ANCHOROUT,'i');
ILM::add_tag('label','ILM',ILM_ANCHOROUT,'L');
ILM::add_tag('option','ILM',ILM_UNREFABLE,'O');
ILM::add_tag('select','ILMblock',ILM_UNREFABLE,'S');

class ILMform extends ILMblock {
	public function __construct(array $base, &$text, $flags=null) {
		ILM::__construct($base,$text,$flags);
		$action = array_popkey($this->attribs,0);
		$this->attribs['method'] = in_array($base[1],array('?','get','getform'))? 'get': 'post';
		$this->attribs['action'] = fixhref($action);
	}
	function openhtml($close=true) {
		ILM::set_flag('inform');
		return ILM::openhtml($close);
	}
	function closehtml() {
		ILM::unset_flag('inform');
		return ILM::closehtml();
	}
};
ILM::add_tag('form','ILMform',ILM_UNREFABLE,'@');
ILM::add_alias('?','form');
ILM::add_alias('getform','form');
ILM::add_alias('get','form');
ILM::add_alias('postform','form');
ILM::add_alias('post','form');

class ILMfieldset extends ILMblock {
	function openhtml($close=true) {
		$r = ILMblock::openhtml($close);
		if(is_string($this->content[0])) {
			$legend = trim(array_shift($this->content));
			$legend = "[legend $legend]";
			$r.= ILM::tohtml($legend);
		}
		return $r;
	}
}
ILM::add_tag('fieldset','ILMfieldset',ILM_UNREFABLE,'@@');

class ILMdinsec extends ILMfieldset {
	function openhtml($close=true) {
		if(ILM::is_flag('inform')) {
			$this->tag = 'fieldset';
			return ILMfieldset::openhtml($close);
		} else {
			$this->tag = 'section';
			return ILMblock::openhtml($close);
		}
	}
}
ILM::add_tag('¶','ILMdinsec');

class ILMsingle extends ILM {
	function openhtml($close=true) {
		if(!empty($this->content)) {
			if(is_string($this->content[0])) {
				$value = array_shift($this->content);
				if(!array_addclass_ifset($this->attribs,$value,'placeholder'))
					$this->attribs['value'] = $value;
			}
			$this->content = array();
		}
		return ILM::openhtml($close);
	}
	function closehtml() {
		return chr(10);
	}
};
ILM::add_tag('br','ILMsingle');
ILM::add_tag('hr','ILMsingle');

class ILMinput extends ILMsingle {
	function setattribs($str) {
		preg_match_all('{([/_\w][-./_\w]*|\W)}', $str, $m);
		while($m[0]) {
			$a = array_shift($m[0]);
			switch($a) {
			case '.';
				$c = array_shift($m[0]);
				$c = str_replace('.',' ',$c);
				array_addclass($this->attribs,$c);
				break;
			case '#';
				$id = array_shift($m[0]);
				$x = explode('.',$id);
				$name = array_shift($x);
				if(empty($x)) {
					$this->attribs['id'] = $name;
				}
				else {
					$value = implode('_',$x);
					$this->attribs['id'] = "{$name}_$value";
					$this->attribs['value'] = $value;
				}
				$this->attribs['name'] = $name;
				break;
			case '<';
				$this->attribs['placeholder'] = array_shift($m[0]);
				break;
			case '@';
				$this->attribs['name'] = array_shift($m[0]);
				$this->attribs['value'] = $this->attribs['id'];
				break;
			case '+';
				$this->attribs['class'] = 'ui ui_'.array_shift($m[0]);
				break;
			case '!';
				$this->attribs['formaction'] = implode($m[0]);
				#echo "<!-- ".print_r($this,true).' -->';
				$m[0]=Null;
				break;
			default;
				$this->attribs[] = $a;
			}
		}
	}
};
ILM::add_tag('input!text','ILMinput',null,'I');
ILM::add_tag('input!password','ILMinput',null,'P');
ILM::add_tag('input!email','ILMinput',null,'email');
ILM::add_tag('input!checkbox','ILMinput',null,'C');
ILM::add_tag('input!radio','ILMinput',null,'R');

class ILMB extends ILM {
	function html() {
		$r = '<b>{</b>';
		$r.= $this->code;
		$r.= '('.implode(':',$this->keys).')';
		$r.= $this->espin? ' ':'';
		$r.= ILM::tohtml($this->content);
		$r.= '<b>}</b>';
		$r.= $this->espafter? ' ':'';
		return $r;
	}
	
	public function __construct(array $base, &$text) {
		$this->code = $base[1];
		#$this->array = $base;
		$keys = explode(':',ltrim($base[2],':'));
		$this->keys = $keys;
		$this->espin = $base[3] != '';
		$this->content = $base[4];
		$this->espafter = preg_match('{^\s}',$text);
		#echo '<!--'.print_r($this,true).'-->';
	}
};

class ILMtext extends ILMB {
	function html() {
		$r = empty($this->keys)? '': array_pop($this->keys);
		$r.= $this->espin? ' ':'';
		$r.= ILM::unscape($this->content);
		$r.= $this->espafter? ' ':'';
		global $i18n;
		return isset($i18n[$r])? $i18n[$r]: $r;
	}
};
ILM::add_brace('text', 'ILMtext');
#print_r(ILM::$braces);

class ILMimg extends ILMB {
	function html() {
		$cl=array();
		$attribs=array(
			'src' => array_pop($this->keys),
		);
		$title = $this->content;
		if((strpos($title,'['))!==false) {
			$caption = ILM::tohtml($title);
		} else {
			$attribs['title'] = $title;
			$attribs['alt'] = $title;
		}
		foreach($this->keys as $key) {
			switch($key) {
			case 'left':
			case 'right':
			case 'center':
				$cl[] = substr($key,0,1);
				break;
			default:
				if(substr($key,0,1)=='#')
					$attribs['id'] = substr($key,1);
				else
					$cl[] = $key;
			}
		}
		if(isset($caption)) {
			array_unshift($cl,'caption');
			$friatts=array('class'=>implode(' ',$cl));
			$r = htmltag('img',HTMLT_STANDALONE,$attribs);
			if(isset($href)) {
				$r = htmltag('a',HTMLT_OPEN,array('href'=>$href)).
					$r.htmltag('a',HTMLT_CLOSE);
			}
			$r = htmltag('div',HTMLT_OPEN,$friatts).
				$r.$caption.htmltag('div',HTMLT_CLOSE);
			return $r;
		} else {
			$attribs['class'] = implode(' ',$cl);
			$r = htmltag('img',HTMLT_STANDALONE,$attribs);
			if(isset($href)) {
				$r = htmltag('a',HTMLT_OPEN,array('href'=>$href)).
					$r.htmltag('a',HTMLT_CLOSE);
			}
			return $r;
		}
	}
};
ILM::add_brace('img', 'ILMimg');

class ILMui extends ILMB {
	function html() {
		#echo '<!--'.print_r($this,true).'-->';
		$cl = 'ui';
		foreach($this->keys as $key)
			$cl.= " ui_$key";
		$content = $this->content;
		return
			htmltag('i',HTMLT_OPEN,array('class'=>$cl)).
			ILM::tohtml($content).
			htmltag('i',HTMLT_CLOSE);
	}
};
ILM::add_brace('ui', 'ILMui');

class ILmath {
static $vars=array();
static function let($var,$val) { ILmath::$vars[$var] = $val; }
static function val($var,$def=0) { return array_get(ILmath::$vars,$var,$def); }
};
class ILMvar extends ILMB {
	function html() {
		try {
			$var = $this->keys[0];
		} catch(Exception $e) {
			return ILM::decode("[W 'for' with too few arguments.]!");
		}
		if(!empty($this->code)) $var = "$code:$var";
		return ILmath::val($var);
	}
};
ILM::add_brace('', 'ILMvar');
class ILMdo extends ILMB {
	function html() {
		if(method_exists($this,$action='do_'.$this->code))
			return $this->$action();
		return ILM::htmlitem($this->block);
	}
	public function __construct(array $base, &$text) {
		ILMB::__construct($base,$text);
		$this->block = array();
		while(!is_a($n=ILM::decode($text),'ILMdone') && !is_a($n,'ILMelse'))
			$this->block[] = $n;
		#echo '<!--'.print_r($this,true).'-->';
	}
	
	function do_for() {
		$r = '';
		$envkeys = $this->keys;
		try {
			$var = array_shift($envkeys);
			$ini = array_shift($envkeys);
			$end = array_pop($envkeys);
		} catch(Exception $e) {
			return ILM::decode("[W 'for' with too few arguments.]!");
		}
		if(!empty($envkeys)) {
			$step = $envkeys[0];
		} else {
			$step = $end<$ini? -1: 1;
		}
		for($i=$ini; ($i*$step)<=($end*$step); $i+=$step) {
			ILmath::let($var,$i);
			$r.= ILM::htmlitem($this->block);
		}
		return $r;
	}
};
class ILMdone extends ILMB {
};
ILM::add_brace('for', 'ILMdo');
ILM::add_brace('next', 'ILMdone');

$tagactions = array(
	'' => 'emptytag',
	'section' => 'sectiontag',
	'§' => 'sectiontag',
	'¶' => 'subsectiontag',
	'@' => 'formtag',
	'?' => 'formtag',
	'form' => 'formtag',
	'@@' => 'fieldsettag',
	'fieldset' => 'fieldsettag',
	'C' => 'inputtag',
	'I' => 'inputtag',
	'H' => 'headingtag',
	'P' => 'inputtag',
	'R' => 'inputtag',
	'X' => 'inputtag',
	'email' => 'inputtag',
	'js' => 'jstag',
);
$tagstack = array();
$hlevel = 0;
$inform = False;

function commonattribs($mods,array &$attribs) {
	preg_match_all('{([/_\w][-./_\w]*|:+|\W)}', $mods, $m);
	while($m[0]) {
		$a = array_shift($m[0]);
		switch($a) {
		case '.';
			$c = array_shift($m[0]);
			$c = str_replace('.',' ',$c);
			array_addclass($attribs,$c);
			break;
		case '#';
			$attribs['id'] = array_shift($m[0]);
			break;
		case '@';
			$f = array_shift($m[0]);
			$attribs['for'] = str_replace('.','_',$f);
			break;
		case ':';
			$attribs['value'] = array_shift($m[0]);
			break;
		case '::';
			$attribs['class'] = 'ui ui_'.array_shift($m[0]);
			break;
		case '!';
			$attribs['href'] = implode($m[0]);
			$m[0]=Null;
			break;
		default;
			$attribs[] = $a;
		}
	}
	return $attribs;
}

function inputattribs($mods,array &$attribs) {
	preg_match_all('{([/\w]+|:+|\W)}', $mods, $m);
	while($m[0]) {
		$a = array_shift($m[0]);
		switch($a) {
		case '.';
			$c = array_shift($m[0]);
			array_addclass($attribs,$c);
			break;
		case '#';
			$attribs['id'] = array_shift($m[0]);
			if(!isset($attribs['name']))
				$attribs['name'] = $attribs['id'];
			if($m[0] && $m[0][0]=='.') {
				array_shift($m[0]);
				$attribs['value'] = array_shift($m[0]);
				$attribs['id'].=  '_'.$attribs['value'];
			}
			break;
		case ':';
			$o = array_shift($m[0]);
			switch($o) {
			case 'x':
				$attribs['checked'] = True;
				break;
			case '-':
				$attribs['disabled'] = True;
				break;
			}
			break;
		case '@';
			$attribs['name'] = array_shift($m[0]);
			break;
		case '>';
			$attribs['placeholder'] = array_shift($m[0]);
			break;
		default;
			$attribs[] = $a;
		}
	}
	return $attribs;
}

$tagalias = array(
	'i'=>'em',
	'b'=>'strong',
	'u'=>'em.ul',
	'l'=>'ul',
	'o'=>'li',
	'L'=>'label',
	'C'=>'input!checkbox',
	'I'=>'input!text',
	'O'=>'option',
	'P'=>'input!password',
	'R'=>'input!radio',
	'S'=>'select',
	'T'=>'textarea',
	'X'=>'input!hidden',
	'email'=>'input!email',
	'content'=>'div.content',
	'c'=>'span.label',
);
function tagalias($alias,&$attribs) {
	global $tagalias;
	$attribs = array();
	if(array_key_exists($alias,$tagalias)) {
		$tagpair = $tagalias[$alias];
		if(($n=strpos($tagpair,'.'))) {
			$attribs['class'] = substr($tagpair,$n+1);
			return substr($tagpair,0,$n);
		} elseif(($n=strpos($tagpair,'!'))) {
			$attribs['type'] = substr($tagpair,$n+1);
			return substr($tagpair,0,$n);
		}
		return $tagpair;
	}
	return $alias;
}

function opentag($tag,&$closetag,&$closefunc) {
	global $tagactions;
	preg_match("{^(§|¶|@+|\?|\w+)?(.*)$}",$tag,$m);
	$t = $m[1];
	$a = array_key_exists($m[1],$tagactions)?
		$tagactions[$t]:
		'defaulttag';
	if(!function_exists($a)) $a='defaulttag';
	return $a($t,$m[2],$closetag,$closefunc);
}

function fixhref($href) {
	return $href;
}
function checkhref(array &$attribs) {
	if(!($href=array_popkey($attribs,'href')))
		return '';
	return '<a href="'.fixhref($href).'">';
}
function defaulttag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'closetag';
	$closetag = tagalias($tag,$attribs);
	commonattribs($mods,$attribs);
	if($p=checkhref($attribs))
		$closefunc = 'closeanchor';
	return $p.htmltag($closetag,$attribs);
}

function emptytag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	if($inform) {
		$closefunc = 'inputclose';
		$attribs = array();
		commonattribs($mods,$attribs);
		$attribs['type'] = 'submit';
		if($action = array_popkey($attribs,'href')) {
			if($action=='!')
				$attribs['type'] = 'reset';
			else
				$attribs['formaction'] = $action;
		}
	} else {
		$closefunc = 'closeemptyanchor';
		$attribs = array();
		commonattribs($mods,$attribs);
	}
	$closetag = $attribs;
	return '';
}

function sectiontag($tag,$mods,&$closetag,&$closefunc) {
	global $hlevel;
	if($hlevel<2) $hlevel=2;
	else $hlevel++;
	$closefunc = 'closesection';
	$closetag = 'section';
	$attribs = array();
	commonattribs($mods,$attribs);
	return htmltag($closetag,$attribs)."<!-- \$hlevel = $hlevel -->";
}

function headingtag($tag,$mods,&$closetag,&$closefunc) {
	global $hlevel;
	if($hlevel<1) $hlevel=1;
	$closefunc = 'closetag';
	$closetag = "h$hlevel";
	$attribs = array();
	commonattribs($mods,$attribs);
	if($p=checkhref($attribs))
		$closefunc = 'closeanchor';
	return $p.htmltag($closetag,$attribs);
}

function formtag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	$inform = true;
	$closefunc = 'closeform';
	$closetag = 'form';
	$attribs = array();
	commonattribs($mods,$attribs);
	$attribs['method'] = $tag=='?'? 'get': 'post';
	array_renamekey($attribs,'href','action') or
	array_renamekey($attribs,0,'action');
	return htmltag($closetag,$attribs);
}

function fieldsettag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'closefieldset';
	$closetag = 'fieldset';
	$attribs = array();
	commonattribs($mods,$attribs);
	return htmltag($closetag,$attribs);
}

function subsectiontag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	return $inform?
		fieldsettag($tag,$mods,$closetag,$closefunc):
		sectiontag($tag,$mods,$closetag,$closefunc);
}

function jstag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'closetag';
	$closetag = "script";
	$attribs = array('type'=>'text/javascript');
	commonattribs($mods,$attribs);
	array_renamekey($attribs,'href','src');
	return htmltag($closetag,$attribs);
}

function inputtag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'inputclose';
	tagalias($tag,$attribs);
	inputattribs($mods,$attribs);
	$closetag = $attribs;
	return '';
}

function inputclose($content, $attribs) {
	if((strpos($content,'<'))!==False)
		return htmltag('button',$attribs).$content."</button>";
	if($c=trim($content)) {
		if(isset($attribs['placeholder']))
			$attribs['placeholder'].=" $c";
		else
			$attribs['value'] = $c;
	}
	return htmltag('input',$attribs);
}

$linebreakinglist="div,header,section,footer,nav,aside";
$linebreakinglist.=",h1,h2,h3,h4,h5,h6,p,form,fieldset";
$linebreakinglist.=",ol,ul,li";
$linebreakings = explode(',',$linebreakinglist);
function closetag($content,$tag) {
	global $linebreakings;
	$r = "$content</$tag>";
	return in_array($tag,$linebreakings)? "$r\n": $r;
}
function closeanchor($content,$tag) {
	global $linebreakings;
	$r = "$content</$tag></a>";
	return in_array($tag,$linebreakings)? "$r\n": $r;
}
function closeemptyanchor($content,$attribs) {
	if(!($c=trim($content)))
		$c = $attribs['href'];
	return htmltag('a',$attribs).$c.'</a>';
}

function closesection($content,$tag) {
	global $hlevel;
	--$hlevel;
	return "$content</$tag>\n";
}

function closeform($content,$tag) {
	global $inform;
	$inform = false;
	return "$content</$tag>\n";
}

function closefieldset($content,$tag) {
	if(preg_match('{^\s*([^<\n"]+)}',$content,$m)) {
		$content = '<legend>'.trim($m[1]).'</legend>'.substr($content,strlen($m[0]));
	}
	return ilm_unscape($content)."</$tag>\n";
}

function openbrace($complex,&$closing,&$ops) {
	$kk = explode(':',$complex);
	if(!$kk[0]) {
		$closing = Null;
		return ilm_var($kk[1]);
	}
	if(preg_match("{^\w+$}",$kk[0])) {
		$closing = $key = array_shift($kk);
		if(function_exists($op="ilmb_$key")) {
			return $op($kk,$ops);
		}
		return "{<b>$key:</b>".implode(":",$kk);
	}
	$closing = 0;
	return "<b style='background:#6cf'>{</b><i style='background:#aaa'>$complex</i>";
}

function closebrace($key,$content,$ops) {
	if(is_null($key))
		return "";
	if(function_exists($op="ilmc_$key"))
		return $op($content,$ops);
	return "$content}";
}

function ilm2html(&$ilm,$o='',$t=Null) {
	$s = '';
	while (preg_match('{^(\s*[^][{}]*)([\x5b\x7b])([^][{}\s]*)(\s*)}',$ilm,$m)) {
		$ilm = substr($ilm,strlen($m[0]));
		$b = $m[2];
		$o = $b.$o;
		$s.= ilm_unscape($m[1]);
		$s.= $b=='['? opentag($m[3],$t,$func): openbrace($m[3],$t,$ops);
		#if($m[4]) $s.= "<tt style='background:#eda'>-</tt>";
		
		$content = ilm2html($ilm,$o,$t);
		
		if (preg_match('{^\s*([^][{}]*)([\x5d\x7d])}',$ilm,$m)) {
			$ilm = substr($ilm,strlen($m[0]));
			$c = $m[2];
			$content.= ilm_unscape($m[1]);
			
			if($c==']') {
				$s.= $b=='['?
					$func($content,$t):
					"\n\n<strong style='border:solid thin red;background-color:yellow>Missmatched $b with $c</strong>\n";
			} else {
				$s.= $b=='{'?
					closebrace($t,$content,$ops):
					"\n\n<strong style='border:solid thin red;background-color:yellow>Missmatched $b with $c</strong>\n";
			}
			$o = substr($o,1);
			#$s.= '<!-- '.$o.'- '.htmlentities(trim(substr($ilm,0,25))).' -->';
		} else {
			$s.= "\n\n<strong style='border:solid thin red;background-color:yellow>Unmatched $b</strong>\n";
		}
	}
	return $s;
}

function ilm_unscape($ilm) {
	$ilm = preg_replace('{\n\s*}',"\n",$ilm);
	$ilm = preg_replace('{\s+}',' ',$ilm);
	$ilm = preg_replace_callback('{(__([0-9A-Fa-f]{1,6})|_[Uu]([0-9A-Fa-f]{4})|_([0-9A-Fa-f]{2}))}','ilme_mb',$ilm);
	return $ilm;
}

function ilme_mb($m) {
	$a = ltrim(array_pop($m),0);
	$p="&#x$a;";
	$r = mb_convert_encoding($p, 'UTF-8', 'HTML-ENTITIES');
	return $r;
}

function ilmb_ui($keys,&$ops) {
	$c = 'ui';
	foreach($keys as $key) {
		$c.=" ui_$key";
	}
	return "<i class=\"$c\">";
}
function ilmc_ui($content,$ops) {
	return "$content</i>";
}

$ilm_vars=array();
function ilm_var($var) {
	global $ilm_vars;
	return array_get($ilm_vars,$var,'0');
}

function ilmb_for($keys,&$ops) {
	global $ilm_vars;
	$ilm_vars[$keys[0]] = $keys[1];
	return '';
}

function ilmb_next($keys,&$ops) {
	return '';
}

function ilmb_img($keys,&$ops) {
	$ops = array();
	$class = array();
	$coor = 0;
	$ops['src'] = array_pop($keys);
	foreach($keys as $key) {
		switch($key) {
		case 'left':
		case 'right':
		case 'center':
			$class[] = substr($key,0,1);
			break;
		case 'auto':
			if($coor==1) {
				$ops['height'] = $key;
				$coor=2;
				break;
			}
			if($coor==0) {
				$ops['width'] = $key;
				$coor=1;
				break;
			}
		default:
			if(substr($key,0,1)=='#') {
				$ops['id'] = substr($key,1);
			} elseif(substr($key,0,1)=='!') {
				$ops['href'] = substr($key,1);
			} elseif(preg_match('{^\d+\w*$}')) {
				if($coor==1) {
					$ops['height'] = $key;
					$coor=2;
					break;
				}
				if($coor==0) {
					$ops['width'] = $key;
					$coor=1;
					break;
				}
			} else {
				$class[] = $key;
			}
		}
	}
	if($class)
		$ops['class'] = implode(' ',$class);
	return '';
}

function ilmc_img($content,$ops) {
	if($h=array_popkey($ops,'href')) {
		$pre = htmltag('a',array('href'=>fixhref($h)));
		$pos = '</a>';
	} else {
		$pre = $pos = '';
	}
	if((strpos($content,'<'))===False) {
		$ops['alt'] = $ops['title'] = trim($content);
		return $pre.htmltag('img',$ops).$pos;
	}
	array_addclass($ops,'caption');
	$src = array_popkey($ops,'src');
	return htmltag('span',$ops).$pre.htmltag('img',array('src'=>$src)).$pos.$content.'</span>';
}

function ilmb_text($keys,&$ops) {
	$ops = implode(':',$keys);
	return '';
}
function ilmc_text($content,$ops) {
	return $ops? "$ops $content": $content;
}

//echo ilm2html($ilm);
echo ILM::tohtml($ilm);
/* */

?>
</html>
