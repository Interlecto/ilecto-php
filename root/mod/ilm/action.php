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
		$text = implode(':',$this->parts);
		if(!empty($this->value))
			$text = trim("$text {$this->value}");
		$tag->addattrib($this->code,$text);
	}
	function html($version=5) { return ''; }
	function text() { return ''; }
}

class ILMtitle extends ILMaction {
	function apply($tag) {
		$first = true;
		$k = $this->parts;
		$f = array_pop($k);
		switch($tag->code) {
		case 'fieldset':
			$c = 'f:legend';
			break;
		case 'table':
			$c = 'caption';
			$first = false;
			break;
		case 'input':
			$c = 'label';
			break;
		case 'div':
			if($tag->hasclass('fgroup')) {
				$c = 'f:label';
				$id = $tag->getatt('id','');
				if(!empty($id))
					$id = "!$id ";
				break;
			}
		default:
			$c = 'p:h';
		}
		array_unshift($k,$c);
		if(!isset($id)) {
			$id = strtolower($f);
			$id = str_replace(' ','_',$id);
			$id = "#$id";
		}
		$o = ILM::read('['.implode('.',$k).$id.'"'.$f.'"]');
		$tag->addchild($o,$first);
	}
}

class ILMkeyaction extends ILMaction {
	function apply($tag) {
		$text = implode(':',$this->parts);
		$text = isset($this->value)? trim("$text {$this->value}"): $text;
		$key = $this->code;
		if(empty($text))
			unset($tag->$key);
		else
			$tag->addkey($key,$text);
	}
}

class ILMbool extends ILMaction {
	function apply($tag) {
		foreach($this->parts as $key) {
			$tag->addattrib($key,true);
		}
	}
}

ILMbrace::add_action('label','ILMkeyaction');
ILMbrace::add_action('style','ILMkeyaction');

?>
