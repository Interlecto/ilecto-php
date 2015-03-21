<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ILM Test</title>
<link rel="stylesheet" href="/style/default/css/style.css" type="text/css" />
<link rel="stylesheet" href="/style/default/css/layout.css" type="text/css" />
<link rel="icon" href="/images/interlecto.ico">
</head>
<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
$ilm = file_get_contents('mod/base/sample.ilm');

function array_popkey(array &$array,$key) {
	if(!isset($array[$key])) return Null;
	$value = $array[$key];
	unset($array[$key]);
	return $value;
}
function array_renamekey(array &$array,$old,$new,$def=Null) {
	if(isset($array[$old])) {
		$array[$new] = $array[$old];
		unset($array[$old]);
		return True;
	}
	if(!is_null($def))
		$array[$new] = $def;
	return False;
}
function array_addclass(array &$array,$class,$key='class') {
	if(isset($array[$key])) $array[$key].=" $class";
	else $array[$key] = $class;
}
function array_get(array $array,$key,$def=null) {
	return isset($array[$key])? $array[$key]: $def;
}

$tagactions = array(
	'' => 'emptytag',
	'section' => 'sectiontag',
	'§' => 'sectiontag',
	'¶' => 'subsectiontag',
	'@' => 'formtag',
	'?' => 'formtag',
	'form' => 'formtag',
	'@@' => 'fieldsettag',
	'fieldset' => 'fieldsettag',
	'C' => 'inputtag',
	'I' => 'inputtag',
	'H' => 'headingtag',
	'P' => 'inputtag',
	'R' => 'inputtag',
	'X' => 'inputtag',
	'email' => 'inputtag',
);
$tagstack = array();
$hlevel = 0;
$inform = False;

function htmltag($tag, array $attribs=Null, $close=True) {
	$r = "<$tag";
	if(!is_null($attribs))
		foreach($attribs as $k=>$v) {
			if(!$v) continue;
			$r.=" $k=";
			if($v===True)
				$r.= $k;
			elseif(preg_match('{^\w+$}', $v))
				$r.= $v;
			else
				$r.= '"'.htmlentities($v).'"';
		}
	if($close) $r.=">";
	return $r;
}
function commonattribs($mods,array &$attribs) {
	preg_match_all('{([/_\w][-./_\w]*|:+|\W)}', $mods, $m);
	while($m[0]) {
		$a = array_shift($m[0]);
		switch($a) {
		case '.';
			$c = array_shift($m[0]);
			$c = str_replace('.',' ',$c);
			array_addclass($attribs,$c);
			break;
		case '#';
			$attribs['id'] = array_shift($m[0]);
			break;
		case '@';
			$f = array_shift($m[0]);
			$attribs['for'] = str_replace('.','_',$f);
			break;
		case ':';
			$attribs['value'] = array_shift($m[0]);
			break;
		case '::';
			$attribs['class'] = 'ui ui_'.array_shift($m[0]);
			break;
		case '!';
			$attribs['href'] = implode($m[0]);
			$m[0]=Null;
			break;
		default;
			$attribs[] = $a;
		}
	}
	return $attribs;
}

function inputattribs($mods,array &$attribs) {
	preg_match_all('{([/\w]+|:+|\W)}', $mods, $m);
	while($m[0]) {
		$a = array_shift($m[0]);
		switch($a) {
		case '.';
			$c = array_shift($m[0]);
			array_addclass($attribs,$c);
			break;
		case '#';
			$attribs['id'] = array_shift($m[0]);
			if(!isset($attribs['name']))
				$attribs['name'] = $attribs['id'];
			if($m[0] && $m[0][0]=='.') {
				array_shift($m[0]);
				$attribs['value'] = array_shift($m[0]);
				$attribs['id'].=  '_'.$attribs['value'];
			}
			break;
		case ':';
			$o = array_shift($m[0]);
			switch($o) {
			case 'x':
				$attribs['checked'] = True;
				break;
			case '-':
				$attribs['disabled'] = True;
				break;
			}
			break;
		case '@';
			$attribs['name'] = array_shift($m[0]);
			break;
		case '>';
			$attribs['placeholder'] = array_shift($m[0]);
			break;
		default;
			$attribs[] = $a;
		}
	}
	return $attribs;
}

$tagalias = array(
	'i'=>'em',
	'b'=>'strong',
	'u'=>'em.ul',
	'l'=>'ul',
	'o'=>'li',
	'L'=>'label',
	'C'=>'input!checkbox',
	'I'=>'input!text',
	'O'=>'option',
	'P'=>'input!password',
	'R'=>'input!radio',
	'S'=>'select',
	'T'=>'textarea',
	'X'=>'input!hidden',
	'email'=>'input!email',
	'content'=>'div.content',
	'c'=>'span.label',
);
function tagalias($alias,&$attribs) {
	global $tagalias;
	$attribs = array();
	if(array_key_exists($alias,$tagalias)) {
		$tagpair = $tagalias[$alias];
		if(($n=strpos($tagpair,'.'))) {
			$attribs['class'] = substr($tagpair,$n+1);
			return substr($tagpair,0,$n);
		} elseif(($n=strpos($tagpair,'!'))) {
			$attribs['type'] = substr($tagpair,$n+1);
			return substr($tagpair,0,$n);
		}
		return $tagpair;
	}
	return $alias;
}

function opentag($tag,&$closetag,&$closefunc) {
	global $tagactions;
	preg_match("{^(§|¶|@+|\?|\w+)?(.*)$}",$tag,$m);
	$t = $m[1];
	$a = array_key_exists($m[1],$tagactions)?
		$tagactions[$t]:
		'defaulttag';
	if(!function_exists($a)) $a='defaulttag';
	return $a($t,$m[2],$closetag,$closefunc);
}

function fixhref($href) {
	return $href;
}
function checkhref(array &$attribs) {
	if(!($href=array_popkey($attribs,'href')))
		return '';
	return '<a href="'.fixhref($href).'">';
}
function defaulttag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'closetag';
	$closetag = tagalias($tag,$attribs);
	commonattribs($mods,$attribs);
	if($p=checkhref($attribs))
		$closefunc = 'closeanchor';
	return $p.htmltag($closetag,$attribs);
}

function emptytag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	if($inform) {
		$closefunc = 'inputclose';
		$attribs = array();
		commonattribs($mods,$attribs);
		$attribs['type'] = 'submit';
		if($action = array_popkey($attribs,'href')) {
			if($action=='!')
				$attribs['type'] = 'reset';
			else
				$attribs['formaction'] = $action;
		}
	} else {
		$closefunc = 'closeemptyanchor';
		$attribs = array();
		commonattribs($mods,$attribs);
	}
	$closetag = $attribs;
	return '';
}

function sectiontag($tag,$mods,&$closetag,&$closefunc) {
	global $hlevel;
	if($hlevel<2) $hlevel=2;
	else $hlevel++;
	$closefunc = 'closesection';
	$closetag = 'section';
	$attribs = array();
	commonattribs($mods,$attribs);
	return htmltag($closetag,$attribs)."<!-- \$hlevel = $hlevel -->";
}

function headingtag($tag,$mods,&$closetag,&$closefunc) {
	global $hlevel;
	if($hlevel<1) $hlevel=1;
	$closefunc = 'closetag';
	$closetag = "h$hlevel";
	$attribs = array();
	commonattribs($mods,$attribs);
	if($p=checkhref($attribs))
		$closefunc = 'closeanchor';
	return $p.htmltag($closetag,$attribs);
}

function formtag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	$inform = true;
	$closefunc = 'closeform';
	$closetag = 'form';
	$attribs = array();
	commonattribs($mods,$attribs);
	$attribs['method'] = $tag=='?'? 'get': 'post';
	array_renamekey($attribs,'href','action') or
	array_renamekey($attribs,0,'action');
	return htmltag($closetag,$attribs);
}

function fieldsettag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'closefieldset';
	$closetag = 'fieldset';
	$attribs = array();
	commonattribs($mods,$attribs);
	return htmltag($closetag,$attribs);
}

function subsectiontag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	return $inform?
		fieldsettag($tag,$mods,$closetag,$closefunc):
		sectiontag($tag,$mods,$closetag,$closefunc);
}

function inputtag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'inputclose';
	tagalias($tag,$attribs);
	inputattribs($mods,$attribs);
	$closetag = $attribs;
	return '';
}

function inputclose($content, $attribs) {
	if((strpos($content,'<'))!==False)
		return htmltag('button',$attribs).$content."</button>";
	if($c=trim($content)) {
		if(isset($attribs['placeholder']))
			$attribs['placeholder'].=" $c";
		else
			$attribs['value'] = $c;
	}
	return htmltag('input',$attribs);
}

$linebreakinglist="div,header,section,footer,nav,aside";
$linebreakinglist.=",h1,h2,h3,h4,h5,h6,p,form,fieldset";
$linebreakinglist.=",ol,ul,li";
$linebreakings = explode(',',$linebreakinglist);
function closetag($content,$tag) {
	global $linebreakings;
	$r = "$content</$tag>";
	return in_array($tag,$linebreakings)? "$r\n": $r;
}
function closeanchor($content,$tag) {
	global $linebreakings;
	$r = "$content</$tag></a>";
	return in_array($tag,$linebreakings)? "$r\n": $r;
}
function closeemptyanchor($content,$attribs) {
	if(!($c=trim($content)))
		$c = $attribs['href'];
	return htmltag('a',$attribs).$c.'</a>';
}

function closesection($content,$tag) {
	global $hlevel;
	--$hlevel;
	return "$content</$tag>\n";
}

function closeform($content,$tag) {
	global $inform;
	$inform = false;
	return "$content</$tag>\n";
}

function closefieldset($content,$tag) {
	if(preg_match('{^\s*([^<\n"]+)}',$content,$m)) {
		$content = '<legend>'.trim($m[1]).'</legend>'.substr($content,strlen($m[0]));
	}
	return ilm_unscape($content)."</$tag>\n";
}

function openbrace($complex,&$closing,&$ops) {
	$kk = explode(':',$complex);
	if(!$kk[0]) {
		$closing = Null;
		return ilm_var($kk[1]);
	}
	if(preg_match("{^\w+$}",$kk[0])) {
		$closing = $key = array_shift($kk);
		if(function_exists($op="ilmb_$key")) {
			return $op($kk,$ops);
		}
		return "{<b>$key:</b>".implode(":",$kk);
	}
	$closing = 'xxx';
	return "<b style='background:#6cf'>{</b><i style='background:#aaa'>$complex</i>";
}

function closebrace($key,$content,$ops) {
	if(is_null($key))
		return "";
	if(function_exists($op="ilmc_$key"))
		return $op($content,$ops);
	return "$content}";
}

function ilm2html(&$ilm,$o='',$t=Null) {
	$s = '';
	while (preg_match('{^(\s*[^][{}]*)([\x5b\x7b])([^][{}\s]*)(\s*)}',$ilm,$m)) {
		$ilm = substr($ilm,strlen($m[0]));
		$b = $m[2];
		$o = $b.$o;
		$s.= ilm_unscape($m[1]);
		$s.= $b=='['? opentag($m[3],$t,$func): openbrace($m[3],$t,$ops);
		#if($m[4]) $s.= "<tt style='background:#eda'>-</tt>";
		
		$content = ilm2html($ilm,$o,$t);
		
		if (preg_match('{^\s*([^][{}]*)([\x5d\x7d])}',$ilm,$m)) {
			$ilm = substr($ilm,strlen($m[0]));
			$c = $m[2];
			$content.= ilm_unscape($m[1]);
			
			if($c==']') {
				$s.= $b=='['?
					$func($content,$t):
					"\n\n<strong style='border:solid thin red;background-color:yellow>Missmatched $b with $c</strong>\n";
			} else {
				$s.= $b=='{'?
					closebrace($t,$content,$ops):
					"\n\n<strong style='border:solid thin red;background-color:yellow>Missmatched $b with $c</strong>\n";
			}
			$o = substr($o,1);
			#$s.= '<!-- '.$o.'- '.htmlentities(trim(substr($ilm,0,25))).' -->';
		} else {
			$s.= "\n\n<strong style='border:solid thin red;background-color:yellow>Unmatched $b</strong>\n";
		}
	}
	return $s;
}

function ilm_unscape($ilm) {
	$ilm = preg_replace('{\n\s*}',"\n",$ilm);
	$ilm = preg_replace('{\s+}',' ',$ilm);
	$ilm = preg_replace_callback('{(__([0-9A-Fa-f]{1,6})|_[Uu]([0-9A-Fa-f]{4})|_([0-9A-Fa-f]{2}))}','ilme_mb',$ilm);
	return $ilm;
}

function ilme_mb($m) {
	$a = ltrim(array_pop($m),0);
	$p="&#x$a;";
	$r = mb_convert_encoding($p, 'UTF-8', 'HTML-ENTITIES');
	return $r;
}

function ilmb_ui($keys,&$ops) {
	$c = 'ui';
	foreach($keys as $key) {
		$c.=" ui_$key";
	}
	return "<i class=\"$c\">";
}
function ilmc_ui($content,$ops) {
	return "$content</i>";
}

$ilm_vars=array();
function ilm_var($var) {
	global $ilm_vars;
	return array_get($ilm_vars,$var,'0');
}

function ilmb_for($keys,&$ops) {
	global $ilm_vars;
	$ilm_vars[$keys[0]] = $keys[1];
	return '';
}

function ilmb_next($keys,&$ops) {
	return '';
}

function ilmb_img($keys,&$ops) {
	$ops = array();
	$class = array();
	$coor = 0;
	$ops['src'] = array_pop($keys);
	foreach($keys as $key) {
		switch($key) {
		case 'left':
		case 'right':
		case 'center':
			$class[] = substr($key,0,1);
			break;
		case 'auto':
			if($coor==1) {
				$ops['height'] = $key;
				$coor=2;
				break;
			}
			if($coor==0) {
				$ops['width'] = $key;
				$coor=1;
				break;
			}
		default:
			if(substr($key,0,1)=='#') {
				$ops['id'] = substr($key,1);
			} elseif(substr($key,0,1)=='!') {
				$ops['href'] = substr($key,1);
			} elseif(preg_match('{^\d+\w*$}')) {
				if($coor==1) {
					$ops['height'] = $key;
					$coor=2;
					break;
				}
				if($coor==0) {
					$ops['width'] = $key;
					$coor=1;
					break;
				}
			} else {
				$class[] = $key;
			}
		}
	}
	if($class)
		$ops['class'] = implode(' ',$class);
	return '';
}

function ilmc_img($content,$ops) {
	if($h=array_popkey($ops,'href')) {
		$pre = htmltag('a',array('href'=>fixhref($h)));
		$pos = '</a>';
	} else {
		$pre = $pos = '';
	}
	if((strpos($content,'<'))===False) {
		$ops['alt'] = $ops['title'] = trim($content);
		return $pre.htmltag('img',$ops).$pos;
	}
	array_addclass($ops,'caption');
	$src = array_popkey($ops,'src');
	return htmltag('span',$ops).$pre.htmltag('img',array('src'=>$src)).$pos.$content.'</span>';
}

function ilmb_text($keys,&$ops) {
	$ops = implode(':',$keys);
	return '';
}
function ilmc_text($content,$ops) {
	return $ops? "$ops $content": $content;
}

echo ilm2html($ilm);
/* */

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
</html>
