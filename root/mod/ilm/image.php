<?php
require_once "brace.php";

class ILMui extends ILMbrace {
	function html($version=5) {
		$cl = "ui";
		$cont = isset($this->value)? ILM::tohtml($this->value,$version): '';
		foreach($this->parts as $p);
			$cl.=" ui_$p";
		return htmltag('i',HTMLT_OPEN,array('class'=>$cl),$version).
			$cont.htmltag('i',HTMLT_CLOSE,null,$version);
	}
}

$GLOBALS['imgpath'] = array(
	'images',
	'',
);
class ILMimg extends ILMbrace {
	function __construct(array &$tokens) {
		ILMbrace::__construct($tokens);
		$src = array_pop($this->parts);
		if(($n=strpos($src,'!'))!==false) {
			$this->link =  substr($src,$n+1);
			$this->src = substr($src,0,$n);
		} else {
			$this->scr = $src;
		}
		if(!isset($_GLOBALS['imgpath_trans'])) {
			global $imgpath,$imgpath_trans;
			$r = $_SERVER['DOCUMENT_ROOT'];
			$imgpath_trans = array();
			foreach($imgpath as $path) {
				$imgpath_trans["/$path"] = rtrim("$r/$path","/");
			}
		}
	}
	function html($version=5) {
		$src = $this->src;
		global $imgpath_trans;
		foreach($imgpath_trans as $ext=>$int) {
			if(file_exists("$int/$src")) {
				$src = "$ext/$src";
				break;
			}
		}
		$atts = array('src'=>$src);
		foreach($this->parts as $p) {
			if(in_array($p,array('left','center','right'))) 
				array_addclass($atts,substr($p,0,1));
			elseif(substr($p,0,1)=='#')
				array_set($atts,'id',substr($p,1));
			else
				array_addclass($atts,$p);
		}
		$img = htmltag('img',HTMLT_STANDALONE,$atts,$version);
		if(isset($this->link))
			$img = htmltag('a',HTMLT_OPEN,array('href'=>$this->link),$version).
				$img.htmltag('a',HTMLT_CLOSE,null,$version);
		return $img;
	}
};

?>
