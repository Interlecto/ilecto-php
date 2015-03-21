<?php

function redirect($absurl,$code=303) {
	header("Location: $url", true, $code);
	return die("$code Redirection");
}

function redirect_local($reluri,$code=303) {
	$url = 'http://'.$_SERVER['HTTP_HOST'];
	if(substr($reluri,0,1)=='/')
		$url.= $reluri;
	else {
		$me = $_SERVER['HTTP_URI'];
		if($i=strrpos($me,'/'))
			$url.= substr($me,0,$i);
		$url.= '/'.$reluri;
	}
	return redirect($url,$code);
}

if(isset($_REQUEST['s-go']))
	return redirect_local( '/search?q=' . urlencode($_REQUEST['s']) );

if(isset($_REQUEST['l-go'])) {
	$user = $_REQUEST['u']);
	$pswd = $_REQUEST['p']);
	$me = $_SERVER['HTTP_URI'];
	redirect_local($me);
}

?>
