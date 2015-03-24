<?php
class tidy {
static $blocks = array(
	'body','div',
	'section','aside','header','footer',
	'table','tbody','thead','tfoot',
	'ul','ol',
);
static $pars = array(
	'p',
	'h1','h2','h3','h4','h5','h6',
	'tr',
);
static $tab="\t";
static function go($str,$version,$tab=null) {
	if(!is_null($tab)) tidy::$tab=$tab;
	$str = preg_replace_callback(
		'{(?:(<(\w+)[^<>]*>)|(</(\w+)>)|(<!--[^<>]*-->))()}',
		function ($match) {
			if(in_array($match[2],tidy::$blocks)) {
				$tab = tidy::$tab;
				tidy::$tab.="\t";
				return chr(10).$tab.$match[0].chr(10).tidy::$tab;
			}
			elseif(in_array($match[2],tidy::$pars)) {
				$tab = tidy::$tab;
				tidy::$tab.="\t";
				return chr(10).$tab.$match[0];
			}
			elseif(in_array($match[4],tidy::$pars)) {
				tidy::$tab = substr(tidy::$tab,0,-1);
				return $match[0].chr(10);
			}
			elseif(in_array($match[4],tidy::$blocks)) {
				tidy::$tab = substr(tidy::$tab,0,-1);
				return chr(10).tidy::$tab.$match[0].chr(10);
			}
			return $match[0];
		},
		$str);
	$str = preg_replace(
		'{\n\t*(\n\t*)}',
		'\1',
		$str);
	$str = preg_replace(
		'{(\n(\t+)<.*\n)([^<\t])}',
		'\1\2\3',
		$str);
	return $str;
}
};
?>
