<?php
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
?>
