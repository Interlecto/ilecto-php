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
		'|'.'"[^"]*"'.
		'|'."'\\w+'".
		'|'.'__[0-9A-Fa-f]{1,6}'.
		'|'.'_[Uu][0-9A-Fa-f]{4}'.
		'|'.'_[0-9A-Fa-f]{2}'.
		'|'.'&\w+;'.
		'|'.'&#\d+;'.
		'|'.'&#x[0-9A-Fa-f]+;'.
		'|'.'\d+'.
		'|'.'[A-Za-z]+'.
		'|'.'\[?[\xc2-\xdf][\x80-\xbf]'.
		'|'.'\[?[\xe0-\xef][\x80-\xbf]{2}'.
		'|'.'\[?[\xf0-\xf7][\x80-\xbf]{3}'.
		'|'.'[\x5b\x7b#:.<>@/][a-z]\w*'.
		'|'.'\[[A-Z]'.
		'|'.'[\x5b\x7b][^][\s\w"#/\x80-\xff]*'.
		'|'.'#+'.
		'|'.':+'.
		'|'.'\W'.
		'}',
		$str,
		$matches);
	$tok = $matches[0];
	#print_r($tok);
	$content = array();
	while(!empty($tok))
		$content[] = ILM::read($tok);
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

	public function __construct($bid, array &$tok) {
		$this->bid = $bid;
	}
	
	function html() {
		return $this->bid;
	}
};

class ILMlit extends ILM {
	public function __construct($bid, array &$tok) {
		$this->bid = trim($bid,'"');
	}
}
ILM::add_pattern('"[^"]*"','ILMlit');

class ILMesp extends ILM {
	public function __construct($bid, array &$tok) {
		$this->bid = substr($bid,0,1)=="\n"? "\n": ' ';
	}
}
ILM::add_pattern('\s.*','ILMesp');

class ILMtagOff extends ILM {
	function html() {
		return "<b>".$this->bid."</b>";
	}
}
ILM::add_pattern('\]','ILMtagoff');
class ILMtag extends ILM {
	function html() {
		return "<b>".$this->bid."</b>".print_r($this,true);
	}
	public function __construct($bid, array &$tok) {
		ILM::__construct($bid,$tok);
		$this->tag = substr($bid,1);
		$this->attribs = array();
		$this->childs=array();
		while(($cl=ILM::get_class($f=array_shift($tok))) != 'ILMtagoff') {
			switch($cl) {
			case 'ILMtag':
			case 'ILMbrace':
			case 'ILMcomment':
				$this->childs[] = new $cl($f,$tok);
				break;
			case 'ILMsubtag':
				$o = new $cl($f,$tok);
				$this->tag.= ':'.$o->sub;
				break;
			case 'ILMesp':
			case 'ILMlit':
				$o = new $cl($f,$tok);
				$f = $o->html();
			default:
				$n = count($this->childs);
				if($n && is_string($this->childs[$n-1]))
					$this->childs[$n-1].= $f;
				else 
					$this->childs[] = $f;
			}
		}
	}
}
ILM::add_pattern('\[.*','ILMtag');

class ILMbraceOff extends ILM {
	function html() {
		return "<i>".$this->bid."</i>";
	}
}
ILM::add_pattern('\}','ILMbraceoff');
class ILMbrace extends ILM {
	function html() {
		return "<i>".$this->bid."</i>";
	}
	public function __construct($bid, array &$tok) {
		ILM::__construct($bid,$tok);
		$this->tag = substr($bid,1);
		$this->childs = array();
		while(($cl=ILM::get_class($f=array_shift($tok))) != 'ILMbraceoff') {
			switch($cl) {
			case 'ILMtag':
			case 'ILMbrace':
				$this->childs[] = new $cl($f,$tok);
				break;
			case 'ILMesp':
			case 'ILMlit':
				$o = new $cl($f,$tok);
				$f = $o->html();
			default:
				$n = count($this->childs);
				if($n && is_string($this->childs[$n-1]))
					$this->childs[$n-1].= $f;
				else 
					$this->childs[] = $f;
			}
		}
	}
};
ILM::add_pattern('\{.*','ILMbrace');

class ILMcomment extends ILMbrace {
	function html() {
		#if($this->show)
			return "<!-- ".htmlentities(trim($this->comment))." -->";
	}
	public function __construct($bid, array &$tok) {
		$this->show = substr($bid,2,1)=='!';
		$this->comment = '';
		while(($cl=ILM::get_class($f=array_shift($tok))) != 'ILMbraceoff') {
			$this->comment.= $f;
		}
		
	}
};
ILM::add_pattern('\{\?.*','ILMcomment');

class ILMsubtag extends ILM {
	public function __construct($bid, array &$tok) {
		$this->sub = substr($bid,1);
	}
}
ILM::add_pattern(':.*','ILMsubtag');

?>
