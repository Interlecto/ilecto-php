<?php
require_once "ilm.php";
require_once "math.php";

class ILMbrace extends ILM {
static $classes = array();
static $actions = array();
static function add_bclass($key,$class){ILMbrace::$classes[$key]=$class;}
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
		$esp = false;
		while($tokens && ($t=array_shift($tokens))!='}') {
			#echo "$t -- $s\n";
			if($esp) {
				$s.= $t;
			} elseif($t==':') {
				$a[] = $s;
				$s = '';
			} elseif(ILM::is_space($t) && !$esp) {
				$a[] = $s;
				$s = '';
				$esp = true;
			} else
				$s.= $t;
		}
		if($esp)
			$this->value = $s;
		else
			$a[] = $s;
		for($i=0;$i<count($a);++$i)
			if(substr($a[$i],0,1)=='(') $a[$i] = ILMmath::make($a[$i]);
		$this->code = array_shift($a);
		$this->parts = $a;
	}
	function html($version=5) { return $this->text(); }
	function text() {
		if(isset($this->value))
			return "&lt;{{$this->code}: {$this->value}}&gt;";
		return "&lt;{{$this->code}}&gt;";
	}
};

class ILMtext extends ILMbrace {
	function __construct(array &$tokens) {
		ILMbrace::__construct($tokens);
		if($f=array_pop($this->parts))
			$this->value = isset($this->value)? trim("$f {$this->value}"): $f;
	}
	#function html($version=5) { return $this->text()."<!-- ".print_r($this,true)." -->"; }
	function text() {
		$f = $this->value;
		global $i18n;
		if(isset($i18n[$f])) return $i18n[$f];
		return $f;
	}
};
ILMbrace::add_bclass('text','ILMtext');

class ILMvar extends ILMbrace {
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
ILMbrace::add_bclass(':','ILMvar');
ILMbrace::add_bclass('alpha','ILMvar');

class ILMpage extends ILMbrace {
	function text() {
		$v = isset($this->value)? $this->value: null;
		return il_get(implode('/',$this->parts),$v);
	}
}

class ILMdoc extends ILMbrace {
	function text() {
		$v = isset($this->value)? $this->value: null;
		return Doc::$first->get(implode('/',$this->parts),$v);
	}
}

?>
