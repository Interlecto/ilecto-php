<?php
require_once "ilm.php";
require_once "math.php";

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
};
ILMbrace::add_class('text','ILMtext');

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

?>
