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
			if(isset($attribs['class']))
				$attribs['class'].= " $c";
			else
				$attribs['class'] = $c;
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
			$attribs['class'].= 'ui ui_'.array_shift($m[0]);
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
			if(isset($attribs['class']))
				$attribs['class'].= " $c";
			else
				$attribs['class'] = $c;
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
	if(isset($id)) $attribs['id'] = $id;
	if(isset($name)) $attribs['name'] = $name;
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
function defaulttag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'closetag';
	$closetag = tagalias($tag,$attribs);
	commonattribs($mods,$attribs);
	if(isset($attribs['href'])) {
		$href = $attribs['href'];
		unset($attribs['href']);
		$closefunc = 'closeanchor';
		return '<a href="'.fixhref($href).'">'.
			htmltag($closetag,$attribs);
	}
	return htmltag($closetag,$attribs);
}

function emptytag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	if($inform) {
		$closefunc = 'inputclose';
		$attribs = array();
		commonattribs($mods,$attribs);
		$attribs['type'] = 'submit';
		if(isset($attribs['href'])) {
			if($attribs['href']=='!')
				$attribs['type'] = 'reset';
			else
				$attribs['formaction'] = $attribs['href'];
			unset($attribs['href']);
		}
		$closetag = $attribs;
		return '';
	} else {
		$closefunc = 'closeemptyanchor';
		$attribs = array();
		commonattribs($mods,$attribs);
		$closetag = $attribs;
		return '';
	}
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
	return htmltag($closetag,$attribs);
}

function formtag($tag,$mods,&$closetag,&$closefunc) {
	global $inform;
	$inform = true;
	$closefunc = 'closeform';
	$closetag = 'form';
	$attribs = array();
	commonattribs($mods,$attribs);
	$attribs['method'] = $tag=='?'? 'get': 'post';
	if(isset($attribs['href'])) {
		$attribs['action'] = $attribs['href'];
		unset($attribs['href']);
	} elseif(isset($attribs[0])) {
		$attribs['action'] = $attribs[0];
		unset($attribs['href']);
	}
	return htmltag($closetag,$attribs);
}

function fieldsettag($tag,$mods,&$closetag,&$closefunc) {
	$closefunc = 'closefieldset';
	$closetag = 'fieldset';
	$attribs = array();
	commonattribs($mods,$attribs);
	return htmltag($closetag,$attribs,False);
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
		$r = ' value="'.trim($m[1]).'">';
		$r.= '<!-- "'.substr($content,0,strlen($m[0])).'" - "'.$m[0].'" -->';
		$r.= substr($content,strlen($m[0]));
	} else {
		$r = '>'.$content;
	}
	return "$r</$tag>\n";
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
	return isset($ilm_vars[$var])? $ilm_vars[$var]: '0';
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
	$ops['src'] = array_pop($keys);
	foreach($keys as $key) {
		switch($key) {
		case 'left':
		case 'right':
		case 'center':
			$class[] = substr($key,0,1);
			break;
		default:
			$class[] = $key;
		}
	}
	if($class)
		$ops['class'] = implode(' ',$class);
	return '';
}

function ilmc_img($content,$ops) {
	if(isset($ops['href'])) {
		$pre = htmltag('a',array('href'=>$ops['href']));
		$pos = '</a>';
		unset($ops['href']);
	} else {
		$pre = $pos = '';
	}
	if((strpos($content,'<'))===False) {
		$ops['alt'] = $ops['title'] = trim($content);
		return $pre.htmltag('img',$ops).$pos;
	}
	if(isset($ops['class'])) $ops['class'].= ' caption';
	else $ops['class'] = 'caption';
	$src = $ops['src'];
	unset($ops['src']);
	return htmltag('span',$ops).$pre.htmltag('img',array('src'=>$src)).$pos.$content.'</span>';
}

echo ilm2html($ilm);
/* */

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
</html>
