<?php
require_once "brace.php";

class ILMui extends ILMtext {
	function html() {
		$cl = "ui";
		$k = $this->parts;
		$l = array_pop($k);
		if(($n=strpos($l,' '))!==false) {
			$cont = substr($l,$n+1);
			$l = substr($l,0,$n);
		}
		array_push($k);
		foreach($this->parts as $p);
			$cl.=" ui_$p";
		return htmltag('i',HTMLT_OPEN,array('class'=>$cl)).
			$cont.htmltag('i',HTMLT_CLOSE);
	}
}

?>
