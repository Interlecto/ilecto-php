<?php
function ilm_escape($input,$chars='_[]{}') {
		$p = preg_quote($chars,'#');
		return preg_replace_callback("#[$p]#",'ilm_escape_char',$input);
}

function ilm_escape_char($c) {
	return sprintf('_%02X',ord($c[0]));
}

function ilm_unescape($p,$t=null,&$text) {
	if(is_array($p) && isset($p[1])) {
		$text = substr($text,3);
		return chr(intval($p[1],16));
	}
	return chr(intval("$p",16));
}

$GLOBALS['ilm2html_subs'] = array(
	'x'=>array(3,'pre'),
	's'=>array(2,'section'),
	'h'=>array(1,'h2'),
	'l'=>array(2,'ul'),
	'i'=>array(1,'li'),
	'p'=>array(1,'p'),
	'f'=>array(1,'form'),
	'lab'=>array(0,'label'),
	'text'=>array(-1,'input','','(type=text)'),
	'passwd'=>array(-1,'input','','(type=password)'),
	'submit'=>array(-1,'input','','(type=submit)'),
	'reset'=>array(-1,'input','','(type=reset)'),
);

class ILMtag {
	public $name,$content,$parent;
	public $tags = array();
	function __construct($tag,$parent=null) {
		$this->name = $tag;
		$this->content = '';
		$this->parent = $parent;
		$this->level = 0;
		if(isset($GLOBALS['ilm2html_subs'][$tag])) {
			$o = $GLOBALS['ilm2html_subs'][$tag];
			if($o[0]>=3) $this->pre = true;
			if(!empty($o[0])) $this->level = $o[0];
			if(!empty($o[1])) $this->alias = $o[1];
			if(!empty($o[2])) $this->add_class($o[2]);
			if(!empty($o[3])) {
				$oa = explode(';',trim($o[3],'[]()'));
				foreach($oa as $ol) {
					$op = explode('=',$ol);
					$ok = array_shift($op);
					$ov = implode('=',$op);
					$this->add_attrib($ok,$ov);
				}
			}
		}
	}
	function build(&$ilmtext) {
		foreach($GLOBALS['ilm_cases'] as $p=>$cb)
			if(preg_match("#^$p#",$ilmtext,$m)) {
				$x = $this;
				$this->content.= $cb($m,$x,$ilmtext);
				return $x;
			}
		$this->content.= $c = substr($ilmtext,0,1);
		$ilmtext=substr($ilmtext,1);
		return $this;
	}
	function set_id($id) {
		$this->id = str_replace('/','-',$id);
	}
	function add_class($class) {
		if(!isset($this->classes)) $this->classes = array();
		$this->classes[] = $class;
	}
	function add_attrib($attrib,$value) {
		if(!isset($this->attributes)) $this->attributes = array();
		$this->attributes[$attrib] = $value;
	}
	
	function html($version=5,$t="\t") {
		$name = $this->name;
		$t0 = $t1 = '';
		$fws = !empty($this->fws);
		$tn = empty($this->alias)? $name: $this->alias;
		$lev = empty($this->level)? 0: $this->level;
		$nt = $lev==2? $t.substr($t,-1): $t;
		if($tn) {
			$t0 = ($lev>0?$t:'')."<$tn";
			if(!empty($this->id)) $t0.= ' id='.$this->id;
			if(!empty($this->classes)) {
				if(count($this->classes)>1)
					$t0.= ' class="'.implode(' ',$this->classes).'"';
				else
					$t0.= ' class='.$this->classes[0];
			}
			if(!empty($this->attributes)) foreach($this->attributes as $k=>$v) {
				$t0.= ' '.$k.'="'.$v.'"';
			}
			$t0.= '>'.($lev==2?chr(10):'');
			if($lev>=0)
				$t1 = ($lev==2?chr(10).$t:'')."</$tn>".($lev?chr(10):($fws?' ':''));
		}
		$s = $t0;
		foreach($this->tags as $t)
			$s.= $t->html($version,$nt);
		if(!empty($this->content)) $s.= $this->content;
		$s.= $t1;
		return $s;
	}
};

class ILMtext extends ILMtag {
	function __construct($parent) {
		ILMtag::__construct(0,$parent);
		$this->content = $parent->content;
		$parent->content = '';
	}
	function html($version=5,$t='') { return $this->content; }
};

class ILMref extends ILMtag {
	function __construct($parent) {
		ILMtag::__construct(0,$parent);
	}
	function html($version=5,$t='') {
		$t0 = $r = $s = $t1 = '';
		
		switch($p = $this->params[0]) {
		case 'mailto':
			$to = $this->params[2];
			$s = empty($this->params[3])? $to: $this->params[3];
			if(preg_match('{([^@]+)@interlecto.net}',$to,$m)) $r = '/contact.cgi/'.$m[1];
			else $r = '/contact.cgi/'.$to;
			break;
		case 'http':
		case 'https':
		case 'ftp':
			$r = $this->params[0].$this->params[1].$this->params[2];
			$s = empty($this->params[3])? trim($this->params[2],'/'): $this->params[3];
			break;
		case 'page':
			$s = il_get(str_replace(':','/',$this->params[2]),$this->content);
			break;
		case 'doc':
			$s = Doc::$first->get(str_replace(':','/',$this->params[2]),$this->content);
			break;
		case 'style':
			$s = html::$first->get(str_replace(':','/',$this->params[2]),$this->content);
			break;
		case 'menu':
		case 'area':
			$k = "$p/".str_replace(':','/',$this->params[2]);
			$s = il_get($k,$this->content);
			break;
		default:
			$s = implode('/',$this->params);
		}
		
		if($this->follow) {
			$t0 = '<a href="'.$r.'">';
			$t1 = '</a>';
		}
		
		return $t0.$s.$t1;
	}
};

$GLOBALS['ilm2html_form'] = array(
	'F'=>array(2,'form'),
	'P'=>array(1,'p'),
	'L'=>array(0,'label'),
	'I'=>array(-1,'input','text'),
	'W'=>array(-1,'input','password'),
	'G'=>array(-1,'input','submit'),
	'R'=>array(-1,'input','reset'),
);

class ILMform extends ILMtag {
	function __construct($tag,$parent=null) {
		$this->name = $tag;
		$this->content = '';
		$this->parent = $parent;
		$this->level = 0;
		if(isset($GLOBALS['ilm2html_form'][$tag])) {
			$o = $GLOBALS['ilm2html_form'][$tag];
			if($o[0]>=3) $this->pre = true;
			if(!empty($o[0])) $this->level = $o[0];
			if(!empty($o[1])) $this->alias = $o[1];
			if(!empty($o[2])) $this->add_attrib('type',$o[2]);
			if(!empty($o[3])) {
				$oa = explode(';',trim($o[3],'[]()'));
				foreach($oa as $ol) {
					$op = explode('=',$ol);
					$ok = array_shift($op);
					$ov = implode('=',$op);
					$this->add_attrib($ok,$ov);
				}
			}
		}
	}
	function set_id($id) {
		$r = strpos($id,'/');
		$name = $r===false? $id: substr($id,0,$r);
		ILMtag::set_id($id);
		if($this->name == 'L') {
			$this->add_attrib('for',$this->id);
			unset($this->id);
		} else {
			$this->add_attrib('name',$name);
		}
	}
	function html($version=5,$t='') {
		if($this->content && $this->level<0) {
			$this->add_attrib('value',$this->content);
			$this->content = '';
		}
		return ILMtag::html($version,$t);
	}
};

class ILM extends ILMtag {
	function __construct($ilmtext) {
		ILMtag::__construct(0);
		$token = $this;
		#$this->content = "<pre>$ilmtext</pre>\n";
		while(!empty($ilmtext) && !empty($token))
			$token = $token->build($ilmtext);
	}

};

function ilm2html($ilm,$version = 5) {
	$I = new ILM($ilm);
	return $I->html($version);#.'<pre style="display:none">'.print_r($I,true).'</pre>'.chr(10);
}

$GLOBALS['ilm_cases'] = array(
	'_([0-9A-Fa-f]{2})' => 'ilm_unescape',
	'\[(\w[-\w]*)(\s*)' => 'ilm_maketag',
	'\.(\w[-\w]*)(\s*)' => 'ilm_tagclass',
	'\#(\w[-/\w]*)(\s*)' => 'ilm_tagid',
	'\](\s*)' => 'ilm_untag',
	'\s+' => 'ilm_whitespace',
	'\{(\w*)(:?)([^{}\n]*)\}' => 'ilm_braces',
	'[A-Za-z]+' => 'ilm_text',
	'\d+' => 'ilm_text',
	'\[\[([-\w]*)(:?)([^][{}\s]*)\s*([^][{}]*)\]\]' => 'ilm_anchor',
	'@\(([^()\n]+)\)(\s*)'=>'ilm_attribs',
	'\[@(\w+)'=>'ilm_form',
);

function ilm_whitespace($m,$t,&$text) {
	$text = substr($text,strlen($m[0]));
	if(empty($t->pre))
		return ' ';
	return $m[0];
}

function ilm_maketag($m,&$t,&$text) {
	$text = substr($text,strlen($m[0]));
	if(!empty($t->content))
		$t->tags[] = $y = new ILMtext($t);
	$t->tags[] = $x = new ILMtag($m[1],$t);
	if(!empty($m[2])) $x->pws = true;
	$t = $x;
	return '';
}

function ilm_form($m,&$t,&$text) {
	$text = substr($text,strlen($m[0]));
	if(!empty($t->content))
		$t->tags[] = $y = new ILMtext($t);
	$t->tags[] = $x = new ILMform($m[1],$t);
	if(!empty($m[2])) $x->pws = true;
	$t = $x;
	return '';
}

function ilm_tagclass($m,$t,&$text) {
	$text = substr($text,strlen($m[0]));
	if(empty($t->pws) && empty($t->content)) {
		$t->add_class($m[1]);
		if(!empty($m[2])) $t->pws = true;
	} else {
		$t->content.= $m[0];
	}
}

function ilm_tagid($m,$t,&$text) {
	$text = substr($text,strlen($m[0]));
	if(empty($t->pws) && empty($t->content) && empty($t->id)) {
		$t->set_id($m[1]);
		if(!empty($m[2])) $t->pws = true;
	} else {
		$t->content.= $m[0];
	}
}

function ilm_untag($m,&$t,&$text) {
	$text = substr($text,strlen($m[0]));
	if(!empty($m[1])) $t->fws = true;
	$t = $t->parent;
}

function ilm_braces ($m,$t,&$text,$active=false) {
	$text = substr($text,strlen($m[0]));
	if(!empty($t->content))
		$t->tags[] = $y = new ILMtext($t);
	$t->tags[] = $x = new ILMref($t);
	$x->content = array_shift($m);
	$x->params = $m;
	$x->follow = $active;
	$x->level = -1;
}

function ilm_text ($m,$t,&$text) {
	$text = substr($text,strlen($m[0]));
	return $m[0];
}

function ilm_anchor ($m,$t,&$text) {
	return ilm_braces($m,$t,$text,true);
}

function ilm_attribs ($m,$t,&$text) {
	$text = substr($text,strlen($m[0]));
	if(empty($t->pws) && empty($t->content)) {
		$r = explode(';',$m[1]);
		foreach($r as $l) {
			$o = explode('=',$l);
			$k = array_shift($o);
			$v = trim(implode('=',$o));
			if(substr($v,0,1)=='"' && substr($v,-1)=='"') $v = substr($v,1,-1);
			elseif(substr($v,0,1)=="'" && substr($v,-1)=="'") $v = substr($v,1,-1);
			$t->add_attrib($k,$v);
		}
		if(!empty($m[2])) $t->pws = true;
	} else {
		$t->content.= $m[0];
	}
}

?>
