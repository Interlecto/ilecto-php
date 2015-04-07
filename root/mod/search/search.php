<?php

require_once "lib/doc.php";
class doc_search extends Doc {
	function prepare_check() {
		$ln = $this->dotlang();
		$docname = "search$ln.cgi";
		$params = il_get('line/params',null);
		$path = is_array($params)? $params[1]: '';
		$q = '?q='.urlencode(il_get('getv/q',''));
		check_or_redirect($path.$docname,null,$q);
	}
	
	function make() {
		$this->set('type','ilm');
		$squery = il_get('getv/q','');
		if(empty($squery)) {
			$this->set_content(13); /* empty search */
			return;
		}
		$this->set_content(14);
		$title = sprintf($this->get('title','Search for "%s"'),$squery);
		$this->set('title',$title);
		il_set('title',$title);
		/* Search logic and results */
	}
};

?>
