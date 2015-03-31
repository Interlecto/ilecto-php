<?php
require_once "brace.php";

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

class ILMelse extends ILMblock {
};
ILMbrace::add_class('elif','ILMelse');

?>
