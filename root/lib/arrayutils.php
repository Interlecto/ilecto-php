<?php

function array_get(array $array,$key,$def=null) {
	return isset($array[$key])? $array[$key]: $def;
}

function array_popkey(array &$array,$key,$def=null) {
	if(!isset($array[$key])) return $def;
	$value = $array[$key];
	unset($array[$key]);
	return $value;
}

function array_renamekey(array &$array,$old,$new,$def=null) {
	if(isset($array[$old])) {
		$array[$new] = $array[$old];
		unset($array[$old]);
		return true;
	}
	if(!is_null($def))
		$array[$new] = $def;
	return false;
}

function array_addclass(array &$array,$class,$key='class') {
	if(isset($array[$key])) $array[$key].=" $class";
	else $array[$key] = $class;
}

function array_addclass_ifset(array &$array,$class,$key='class') {
	if(isset($array[$key])) return $array[$key].=" $class";
	else return false;
}

define('ASET_UNSET',0);
define('ASET_EMPTY',1);
define('ASET_ALWAYS',2);
function array_set(array &$array,$key,$value,$how = ASET_EMPTY) {
	if(!empty($array[$key]) && $how>=ASET_ALWAYS) $array[$key]=$value;
	elseif(isset($array[$key]) && $how>=ASET_EMPTY) $array[$key]=$value;
	else $array[$key]=$value;
}

?>
