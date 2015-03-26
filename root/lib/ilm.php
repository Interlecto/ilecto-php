<?php
require_once './arrayutils.php';

function str_assess(&$str) {
	try {
		$str = "$str";
	} catch (Exception $e) {
		echo "Not a string.\n";
	}
}

class ILM {
static $patterns = array('');
static $classes = array('ILM');
static $all_patterns = '{(?:(^$))}';
static function add_pattern($pattern,$class) {
	array_unshift(ILM::$patterns,$pattern);
	array_unshift(ILM::$classes,$class);
	ILM::$all_patterns = '{(?:(^'.
		implode('$)|(^',ILM::$patterns).
		'$))}';
}

static function get_class($str) {
	str_assess($str);
	if(!preg_match(ILM::$all_patterns,$str,$m)) {
		return 'ILM';
	}
	$c = substr(array_shift($m),0,1);
	if(empty($c)) return 'ILM';
	$k = implode("\x01",$m);
	$n = strpos($k,$c);
	return ILM::$classes[$n];
}

static function tohtml(&$str) {
	str_assess($str);
	preg_match_all(
		'{'.'\n\s*'.
		'|'.'\s+'.
		'|'.'"[^"\n]*"'.
		'|'."'\\w+'".
		'|'.'![A-Za-z#/.][^][{}\s]*'.
		'|'.'__[0-9A-Fa-f]{1,6}'.
		'|'.'_[Uu][0-9A-Fa-f]{4}'.
		'|'.'_[0-9A-Fa-f]{2}'.
		'|'.'&\w+;'.
		'|'.'&#\d+;'.
		'|'.'&#x[0-9A-Fa-f]+;'.
		'|'.'\d+'.
		'|'.'[A-Za-z]+'.
		'|'.'[\[:]?[\xc2-\xdf][\x80-\xbf]'.
		'|'.'[\[:]?[\xe0-\xef][\x80-\xbf]{2}'.
		'|'.'[\[:]?[\xf0-\xf7][\x80-\xbf]{3}'.
		'|'.'[\x5b\x7b#:.<>@/][a-z]\w*'.
		'|'.'[\x5b\x7b#:.<>@/]"[^"\n]*"'.
		'|'.'\[[A-Z]'.
		'|'.'[\x5b\x7b][^][\s\w"#/:!\x80-\xff]*'.
		'|'.'#+'.
		'|'.':+'.
		'|'.'\W'.
		'}',
		$str,
		$matches);
	$tok = $matches[0];
	#print_r($tok);
	$content = array();
	while(!empty($tok)) {
		#var_dump($tok[0]);
		$content[] = ILM::read($tok);
	}
	$response = '';
	foreach($content as $item) {
		$response.= $item->html();
	}
	return $response;
}

static function read(array &$tok) {
	$first = array_shift($tok);
	$class = ILM::get_class($first);
	return new $class ($first,$tok);
}

	public $bid, $target;
	public function __construct($bid, array &$tok=null) {
		$this->bid = $bid;
		$this->target = 'text';
	}
	
	function html() {
		return $this->bid;
	}

	function text() {
		return $this->bid;
	}

	function tagtarget() {
		return $this->target;
	}
};

class ILMlit extends ILM {
	public function __construct($bid, array &$tok=null) {
		$this->bid = trim($bid,'"');
		$this->target = 'text';
	}
}
ILM::add_pattern('"[^"]*"','ILMlit');

class ILMesp extends ILM {
	public function __construct($bid, array &$tok) {
		$this->bid = substr($bid,0,1)=="\n"? "\n": ' ';
		$this->target = 'text';
	}
}
ILM::add_pattern('\s.*','ILMesp');

class ILMtagOff extends ILM {
	function html() {
		return "";
	}
	public function __construct($bid, array &$tok) {
		$this->bid = '';
		$this->target = '';
	}
}
ILM::add_pattern('[\x5d\x7d]','ILMtagOff');

class ILMtag extends ILM {
	function html() {
		$r = '<'.$this->tag;
		foreach($this->attribs as $k=>$v) {
			$r.=" $k=\"$v\"";
		}
		$r.='>';
		if(isset($this->value))
			$r.= $this->value;
		else
			foreach($this->childs as $child) {
				$r.= $child->html();
			}
		$r.= '</'.$this->tag.'>';
		return $r;
	}
	public function __construct($bid, array &$tok) {
		ILM::__construct($bid,$tok);
		$this->target = 'child';
		$this->tag = substr($bid,1);
		#echo $this->tag.chr(10);
		$this->attribs = array();
		$this->childs=array();
		$s = '';
		do {
			$cl=ILM::get_class($f=array_shift($tok));
			if($cl=='ILMtagOff') break;
			$o = new $cl($f,$tok);
			switch($t = $o->tagtarget()) {
			case 'text':
				$s.= $o->text();
				break;
			case 'child':
				if(!empty($s)) {
					$this->childs[] = new ILM("$s");
					$s = '';
				}
				$this->childs[] = $o;
			case 'off':
			case '':
				break;
			case 'tag':
				if(empty($s) && empty($this->childs))
					$this->tag.= $o->text();
				else
					$s.= $o->text();
				break;
			case 'id':
				$x = $o->text();
				$this->attribs[$t] = $x=='#'? $this->tag: $x;
				break;
			case 'class':
				array_addclass($this->attribs, $o->text(), $t);
				break;
			default:
				$this->attribs[$t] = $o->text();
			}
			#echo " '$f' '$cl' '$t' \n";
		} while($t!='');
		if(!empty($s)) {
			if(empty($tihs->childs))
				$this->value = $s;
			else
				$this->childs[] = $s;
		}
		while(($cl=ILM::get_class($f=$tok[0]))=='ILMesp')
			array_shift($tok);
	}
	
	function text() {
		if(isset($this->value)) return $this->value;
		if(empty($this->childs))
			return "[{$this->tag}]";
		$r = '';
		foreach($this->childs as $child)
			$r.= $child->text();
		return $r;
	}
}
ILM::add_pattern('\[.*','ILMtag');

class ILMbrace extends ILM {
	function html() {
		return "<i>".$this->bid."</i>";
	}
	public function __construct($bid, array &$tok) {
		ILM::__construct($bid,$tok);
		$this->tag = substr($bid,1);
		#echo $this->tag.chr(10);
		$this->attribs = array();
		$this->childs=array();
		$s = '';
		do {
			$cl=ILM::get_class($f=array_shift($tok));
			$o = new $cl($f,$tok);
			switch($t = $o->tagtarget()) {
			case 'text':
				$s.= $o->text();
				break;
			case 'child':
				if(!empty($s)) {
					$this->childs[] = new ILM("$s");
					$s = '';
				}
				$this->childs[] = $o;
			case 'off':
			case '':
				break;
			case 'tag':
				if(empty($s) && empty($this->childs))
					$this->tag.= $o->text();
				else
					$s.= $o->text();
				break;
			case 'link':
				$this->link = $o->text();
				break;
			case 'class':
				array_addclass($this->attribs, $o->text(), $t);
				break;
			default:
				$this->attribs[$t] = $o->text();
			}
		} while($t!='');
		if(!empty($s) && empty($tihs->childs))
			$this->value = $s;
		if(($cl=ILM::get_class($f=$tok[0]))=='ILMesp')
			array_shift($tok);
	}
};
ILM::add_pattern('\{.*','ILMbrace');

class ILMcomment extends ILMbrace {
	function html() {
		#if($this->show)
			return "<!-- ".htmlentities(trim($this->comment))." -->";
	}
	public function __construct($bid, array &$tok) {
		$this->bid = '';
		if(substr($bid,2,1)=='!') {
			$this->target = 'child';
			$this->show = true;
		} else {
			$this->target = 'off';
			$this->show = false;
		}
		$s = '';
		do {
			$cl=ILM::get_class($f=array_shift($tok));
			$o = new $cl($f,$tok);
			$t = $o->tagtarget();
			$s.= $o->text();
			#echo " '$f' '$cl' '$t' \n";
		} while($t!='');
		$this->comment = $s;
	}
};
ILM::add_pattern('\{\?.*','ILMcomment');

class ILMsubtag extends ILM {
	public function __construct($bid, array &$tok) {
		$this->bid = $bid;
		$this->sub = trim(substr($bid,1),'" ');
		$this->target = 'tag';
	}
};
ILM::add_pattern(':.*','ILMsubtag');

class ILMclass extends ILMsubtag {
	public function __construct($bid, array &$tok) {
		ILMsubtag::__construct($bid,$tok);
		$this->bid = $this->sub;
		$this->target = 'class';
	}
};
ILM::add_pattern('\.\w.*','ILMclass');

class ILMid extends ILMsubtag {
	public function __construct($bid, array &$tok) {
		ILMsubtag::__construct($bid,$tok);
		$this->bid = $this->sub;
		$this->target = 'id';
	}
};
ILM::add_pattern('#.*','ILMid');

class ILMlink extends ILMsubtag {
	public function __construct($bid, array &$tok) {
		ILMsubtag::__construct($bid,$tok);
		$this->bid = $this->sub;
		$this->target = 'link';
	}
};
ILM::add_pattern('!.+','ILMlink');

class ILMph extends ILMsubtag {
	public function __construct($bid, array &$tok) {
		ILMsubtag::__construct($bid,$tok);
		$this->bid = $this->sub;
		$this->target = 'placeholder';
	}
};
ILM::add_pattern('<.+','ILMph');

class ILMfor extends ILMsubtag {
	public function __construct($bid, array &$tok) {
		ILMsubtag::__construct($bid,$tok);
		$this->bid = $this->sub;
		$this->target = 'for';
	}
};
ILM::add_pattern('>.+','ILMfor');


?>
