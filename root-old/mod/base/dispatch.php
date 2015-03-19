<?php

// alias for http_redirect. Allows to comment out the actual redirect for debugging processes
function redirect($url, $params=null, $session=false, $status=0) {
	if(!$params) $params=array();
	if(function_exists('http_redirect')) return http_redirect($url,$params,$session,$status);
	while(@ob_end_clean());
	if(preg_match('{^\w+://}',$url)) {
		$fullurl = $url;
	} else {
		$protocol = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']!='on' ? 'http://': 'https://';
		$host = $_SERVER['HTTP_HOST'];
		if(substr($url,0,1)=='/') {
			$fullurl = $protocol.$host.$url;
		} else {
			$dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$cpi = pathinfo(il_get('line/uri',''));
			$dir = (empty($cpi['dirname']) || $cpi['dirname']=='.' || $cpi['dirname']=='/' || $cpi['dirname']=='\\')?
				'/': '/'.$cpi['dirname'].'/';
			$fullurl = $protocol.$host.$dir.$url;
		}
	}
	header("Location: $fullurl");
	die( "Redirect to <a href=\"$fullurl\">$url</a> (from ".il_get('request/uri').")" );
}

function check_or_redirect($go,$pat=null) {
	$l = ltrim(il_get('request/uri',il_get('line/clean')),'/');
	if(($n = strpos($l,'?'))!==false) $l = substr($l,0,$n);
	if(!empty($pat))
		$o = preg_replace($go,$pat,$l);
	else
		$o = ltrim($go,'/');
	if($o!=$l) redirect('/'.$o);
}

require_once 'lib/doc.php';
class doc_dispatch extends Doc {
	function __construct($page) {
		/* Any logic for redirecting purposes should come here. */
		/* otherwise */
		Doc::__construct($page);
	}
	
	function make() {
		make_status($this,404);
	}
};

?>
