<?php
require_once "brace.php";

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
?>
