<?php
define('SET_REPLACE',1);
define('SET_EMPTY',2);
define('SET_UNSET',3);
define('DEF_EMPTY',2);
define('DEF_UNSET',3);
define('ADD_ARRAY',1);
define('ADD_STRING',2);
define('ADD_NUMBER',3);

class Attributer {
	//public $C=array();
	function __construct($protect=false) {
		if($protect) {
			$this->protect=array('protect');
			if(is_array($protect)) $this->protect = array_merge($this->protect,$protect);
			if(is_string($protect)) $this->protect[] = $protect;
		}
	}
	
	private function deeper(&$key,&$atr,$create=false) {
		if(($n=strpos($key,'/'))!=false) {
			$root = substr($key,0,$n);
			$key  = substr($key,1+$n);
			if(isset($this->$root))
				$atr = $this->$root;
			elseif($create)
				$atr = $this->$root = new Attributer();
			else
				$atr = $root;
			return true;
		}
		return false;
	}
	
	function set($key,$val,$how=SET_REPLACE) {
		if(isset($this->protect) && in_array($key,$this->protect)) return null;
		if($this->deeper($key,$atr,true))
			return $atr->set($key,$val,$how);
		if(method_exists($this,$met=$key.'_set')) return $this->$met($val,$how);
		if($how==SET_REPLACE || !isset($this->$key)) $this->$key = $val;
		elseif($how==SET_EMPTY && empty($this->$key)) $this->$key = $val;
		return $this->$key;
	}
	
	function get($key,$default=null,$how=DEF_UNSET) {
		if($this->deeper($key,$atr)) {
			if(is_a($atr,'Attributer')) return $atr->get($key,$default,$how);
			if(is_array($atr)) return isset($atr[$key])? $atr[$key]: $default;
			if(method_exists($this,$met="{$atr}_get")) return $this->$met($key,$default,$how);
			if(method_exists($this,$met="$atr")) return ($r = $this->$met($key))? $r: $default;
			return $default;
		}
		if(method_exists($this,$met=$key.'_get')) return $this->$met($default,$how);
		if(method_exists($this,$key)) return ($r = $this->$key())? $r: $default;
		if(!isset($this->$key)) return $default;
		return empty($this->$key) && $how==DEF_EMPTY? $default: $this->$key;
	}
	
	function add($key,$val=null,$how=ADD_ARRAY) {
		if(isset($this->protect) && in_array($key,$this->protect)) return null;
		if($this->deeper($key,$atr,true))
			return $atr->add($key,$val,$how);
		if(method_exists($this,$met=$key.'_add')) return $this->$met($val,$how);
		if(!isset($this->$key)) {
			switch($how) {
			case ADD_ARRAY:  $this->$key = array(); break;
			case ADD_STRING: $this->$key = ''; break;
			case ADD_NUMBER: $this->$key = 0; break;
			default:
				if(is_null($val)) return null;
				if(is_number($val)) return $this->$key = (double)$val;
				if(is_string($val)) return $this->$key = trim($val).chr(10);
				return $this->$key = array($val);
			}
		}
		if(is_null($val)) {}
		elseif(is_array ($this->$key)) array_push($this->$key,$val);
		elseif(is_string($this->$key)) $this->$key.= trim("$val").chr(10);
		elseif(is_number($this->$key)) $this->$key+= (double)$val;
		return $this->$key;
	}
	
	function exists($key) {
		if($this->deeper($key,$atr)) {
			if(is_a($atr,'Attributer')) return $atr->exists($key);
			if(is_array($atr)) return isset($atr[$key]);
			return false;
		}
		return isset($this->$key);
	}
	
	function empt($key) {
		if($this->deeper($key,$atr)) {
			if(is_a($atr,'Attributer')) return $atr->empt($key);
			if(is_array($atr)) return empty($atr[$key]);
			return false;
		}
		return empty($this->$key);
	}
	
	function clear($key) {
		if(isset($this->protect) && in_array($key,$this->protect)) return null;
		if($this->deeper($key,$atr)) {
			if(is_a($atr,'Attributer')) $atr->clear($key);
			elseif(is_array($atr)) unset($atr[$key]);
		} else unset($this->$key);
	}
	
	function as_array() {
		$je = json_encode($this);
		return json_decode($js,true);
	}
	
	function from_array($key,$arr) {
		$this->$key = new Attributer();
		foreach($arr as $k=>$v) {
			if(is_array($v))
				$this->$key->from_array($k,$v);
			else
				$this->$key->set($k,$v);
		}
	}
}
?>
