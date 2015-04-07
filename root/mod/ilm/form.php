<?php
require_once 'tag.php';

class ILMfbase extends ILMtag {
	function __construct($code,array &$tokens) {
		$p = empty(ILM::$tagstack)? null: ILM::$tagstack[0];
		ILMtag::__construct($code,$tokens);
		if(isset($p->style))
			$this->style = $p->style;
		ILM::ltrim($tokens);
	}
	function htmlopen($version=5) {
		if($this->hasclass('fgroup') && isset($this->style))
			$this->addclass('f_'.$this->style);
		return ILMtag::htmlopen($version);
	}
}

class ILMform extends ILMfbase {
	function htmlopen($version=5) {
		if($this->code=='f') $this->code = 'form';
		if($this->code=='get') {
			$this->code = 'form';
			$this->method = 'get';
		}
		$this->addattrib('method',isset($this->method)?$this->method:'post');
		if(isset($this->link))
			$this->addattrib('action',fixhref($this->link));
		return ILMfbase::htmlopen($version);
	}
};

class ILMlabeled extends ILMfbase {
	function setlabel($version=5) {
		if(!$this->hasatt('name'))
			$this->addattrib('name',$this->getatt('id'));
		$pre = $post = '';
		if(isset($this->label)) {
			$this->label = $label = htmltag_content('label',$this->label,array('for'=>$this->getatt('id')),$version);
			if(isset($this->style)) {
				$divop = htmltag('div',HTMLT_OPEN,array('class'=>'fgroup f_'.$this->style),$version);
				$divcl = htmltag('div',HTMLT_CLOSE,null,$version);
				switch($this->style) {
				case 'radios':
					$pre = $divop;
					$post = $label.$divcl;
					break;
				default:
					$pre = $divop.$label;
					$post = $divcl;
				}
			} else {
				if(isset($this->type) && in_array($this->type,array('radio','checkbox')))
					$post = $label;
				else
					$pre = $label;
			}
		}
		$this->pretag = $pre;
		$this->postag = $post;
	}
	function htmlopen($version=5) {
		$this->setlabel($version);
		return $this->pretag.ILMfbase::htmlopen($version);
	}
	function htmlclose($version=5) {
		return ILMfbase::htmlclose($version).$this->postag;
	}
	function htmlstandalone($version=5) {
		$this->setlabel($version);
		return $this->pretag.
			ILMfbase::htmlstandalone($version).
			$this->postag;
	}
};

class ILMinput extends ILMlabeled {
	function __construct($code,array &$tokens) {
		ILMfbase::__construct($code,$tokens);
		$type = $this->code;
		$this->code = 'input';
		$this->type = $type;
		ILM::ltrim($tokens);
	}
	function html($version=5) {
		$type = $this->type;
		$this->addattrib('type',$type);
		$r = ILMcontainer::html($version);
		if(preg_match('{<\w+}',$r)) {
			if(in_array($type,array('submit','reset','button'))) {
				return
					htmltag('button',HTMLT_OPEN,$this->attribs,$version).
					$r.htmltag('button',HTMLT_CLOSE,null,$version);
			}
			$r = ILMcontainer::text($version);
		}
		if(!empty($r))
			$this->addattrib('value',$r);
		return $this->htmlstandalone($version);
	}
};

class ILMselect extends ILMlabeled {
	/*function htmlopen($version=5) {
		if(!$this->hasatt('name'))
			$this->addattrib('name',$this->getatt('id'));
		return ILMfbase::htmlopen($version);
	}/**/
};

class ILMlabel extends ILMfbase {
	function htmlopen($version=5) {
		if(isset($this->link)) $this->addattrib('for',ltrim($this->link,'#'));
		return ILMfbase::htmlopen($version);
	}
};

ILM::add_namespace('f','ILMform','form');
ILM::add_class('text','ILMinput','f','i');
ILM::add_class('submit','ILMinput','f','g');
ILM::add_class('reset','ILMinput','f');
ILM::add_class('password','ILMinput','f');
ILM::add_class('email','ILMinput','f');
ILM::add_class('radio','ILMinput','f','r');
ILM::add_class('checkbox','ILMinput','f','c');
ILM::add_class('fieldset','ILMfbase','f','set');
ILM::add_class('div','ILMfbase','f');
ILM::add_class('div.fgroup','ILMfbase','f','group');
ILM::add_class('select','ILMselect','f','s');
ILM::add_class('option','ILMfbase','f','o');
ILM::add_class('label','ILMlabel','f');
ILM::add_class('textarea','ILMlabeled','f','t');
ILM::add_class('get','ILMform','f');


?>
