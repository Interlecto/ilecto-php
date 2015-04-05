<?php

function make_absolute_url($reluri) {
	$url = $_SERVER['REQUEST_SCHEME'].'://';
	$url.= $_SERVER['HTTP_HOST'];
	if(substr($reluri,0,1)=='/')
		$url.= $reluri;
	else {
		$me = $_SERVER['REQUEST_URI'];
		if($i=strrpos($me,'/'))
			$url.= substr($me,0,$i);
		$url.= '/'.$reluri;
	}
	return $url;
}

function redirect_catch($absurl,$code=303) {
	header("Location: $url", true, $code);
	return die("$code Redirection");
}

function redirect_local($reluri,$code=303) {
	$url = make_absolute_url($reluri);
	return redirect_catch($url,$code);
}

function forward_post($reluri, array $data, array $headers = null) {
	$url = make_absolute_url($reluri);
	$params = array(
		'http' => array(
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	if (!is_null($headers)) {
		$params['http']['header'] = '';
		foreach ($headers as $k => $v) {
			$params['http']['header'] .= "$k: $v\n";
		}
	}
	$ctx = stream_context_create($params);
	if($fp = @fopen($url, 'rb', false, $ctx)) {
		return @stream_get_contents($fp);
	}
	throw new Exception("Error loading '$url', $php_errormsg");
}

/*
if(isset($_REQUEST['s-go']))
	redirect_local( '/search?q=' . urlencode($_REQUEST['s']) );

if(isset($_REQUEST['l-go'])) {
	$me = $_SERVER['REQUEST_URI'];
	$params = array(
		'u' => $_REQUEST['u'],
		'p' => $_REQUEST['p'],
		'l' => $me
	);
	$ans = forward_post('/login',$params);
	#if($ans==0)
	#	redirect_local($me);
	echo "<!-- CATCHED LOGIN -->";
	echo "<!-- ".print_r($_SERVER,true)." -->";
	echo $ans;
	die ();
}
* */

?>
