<?php
require_once 'tag.php';

class ILMpar extends ILMtag {
	function __construct($code,array &$tokens) {
		ILMtag::__construct($code,$tokens);
		$tag = $this->code;
		$ns = empty($this->namespace)? 'p': $this->namespace;
		if(!isset(ILM::$nsdic[$ns][$tag]) && !isset(ILM::$nsalias[$ns][$tag])) {
			$dt = ILM::$nsalias[$ns][''];
			ILM::$nsalias[$ns][$tag] ="$dt.li.$tag";
			$this->code = $dt;
			$this->addclass("li $tag");
		}
	}
};
ILM::add_namespace('p','ILMpar');
#ILM::add_class('h2',null,'p','h');
#ILM::add_class('p.bullet',null,'p','bullet');
ILM::add_class('table.fancy',null,'t','fancy');
ILM::add_class('thead',null,'t','head');
ILM::add_class('tbody',null,'t','body');
ILM::add_class('tr',null,'t','row');
ILM::add_class('th',null,'t','h');
ILM::add_class('td',null,'t','d');

class ILMlinkin extends ILMtag {
	function htmlopen($version=5) {
		$anchor = isset($this->link)?
			htmltag('a',HTMLT_OPEN,array('href'=>$this->link),$version):
			'';
		return ILMtag::htmlopen().$anchor;
	}
	function htmlclose($version=5) {
		$canchor = isset($this->link)?
			htmltag('a',HTMLT_CLOSE):
			'';
		return $canchor.ILMtag::htmlclose();
	}
};

class ILMheading extends ILMlinkin {
	function __construct($code,array &$tokens) {
		$level = isset(ILM::$flags['level'])? ILM::$flags['level']: 1;
		$tagname = "h$level";
		ILMlinkin::__construct($tagname,$tokens);
	}
};
ILM::add_class('h','ILMheading','p');

class ILMlinkout extends ILMtag {
	function htmlopen($version=5) {
		$anchor = isset($this->link)?
			htmltag('a',HTMLT_OPEN,array('href'=>$this->link),$version):
			'';
		return $anchor.ILMtag::htmlopen();
	}
	function htmlclose($version=5) {
		$canchor = isset($this->link)?
			htmltag('a',HTMLT_CLOSE):
			'';
		return ILMtag::htmlclose().$canchor;
	}
};


?>
