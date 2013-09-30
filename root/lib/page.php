<?php
require_once 'lib/attributer.php';
require_once 'lib/db.php';
require_once 'lib/doc.php';

class Page extends Attributer {
	static $first;
	
	function __construct() {
		session_start();
		Attributer::__construct(1);
		if(!isset(Page::$first)) Page::$first = $this;
		$this->protect[] = 'db';
		$this->getglobals();
		$this->queries = "--\n";
		$this->open_db();
		$this->get_db_params();
		$this->imports();
		get_user();
	}
	function close() {
		$this->db->close();
	}
	
	function getglobals() {
		$this->line = new Attributer();
		foreach($GLOBALS as $gvar=>$val)
			if(substr($gvar,0,3)=='il_') {
				$var = str_replace('_','/',substr($gvar,3));
				$this->set($var,$val);
				unset($gvar);
			}
		foreach($_SERVER as $serkey=>$val) {
			$key = preg_match('{^(HTTP|SERVER|CONTEXT|REDIRECT|REQUEST|PHP)_(.*)}',$serkey,$m)? strtolower("{$m[1]}/{$m[2]}"): strtolower("serv/$serkey");
			$this->set($key,$val);
		}
		foreach($_REQUEST as $key=>$val) {
			$this->set("req/$key",$val);
		}
		foreach($_SESSION as $key=>$val) {
			$this->set("session/$key",$val);
		}
		$this->set('line/uri',$this->get('req/line',$this->get('redirect/url',$this->get('request/uri',''))));
		$this->set('line/query',$this->get('redirect/query_string',$this->get('serv/query_string','')));
	}
	function get_db_params() {
		$params = $this->db->select('gen_param',array('param','idx','value'),array('object'=>'=1'));
		if($params)
		foreach($params as $row) {
			$key = $row['idx']==1? $row['param']: $row['param'].'/'.$row['idx'];
			$this->set($key,$value,SET_EMPTY);
		}
		$cases = $this->db->select('res_case');
		if($cases) {
			$line = $this->get('line/uri');
			$p = -1;
			foreach($cases as $row) {
				$re = '#^/?('.$row['case'].')(\?|$)#';
				$q = preg_match($re,$line,$m);
				if($q && $p<$row['priority']) {
					array_shift($m);array_pop($m);
					$this->set('line/clean',array_shift($m));
					$this->set('line/engine',$row['engine']);
					if(!empty($row['langidx'])) {
						$ll = explode(',',$row['langidx']);
						foreach($ll as $n) {
							if(!empty($m[$n])) {
								$this->set('line/lang',$m[$n]);
								$this->set('lang',$m[$n]);
								break;
							}
						}
					}
					if(!empty($row['formidx'])) {
						$fl = explode(',',$row['formidx']);
						foreach($fl as $n) {
							if(!empty($m[$n])) {
								$this->set('line/ext',$m[$n]);
								$this->set('format',$m[$n]);
								break;
							}
						}
					}
					$this->set('line/params',$m);
					$p = $row['priority'];
				}
			}
		}
	}
	function open_db() {
		$server	= $this->get('data/server','localhost');
		$user	= $this->get('data/user','root');
		$passwd	= $this->get('data/password','');
		$db		= $this->get('data/database','interlecto');
		$prefix	= $this->get('data/prefix','');
		$this->db = new db($server,$user,$passwd,$db,$prefix);
	}
	
	function checkstyle(&$style,&$format) {
		if(file_exists($fn="style/$style/$format.php")) return $fn;
		if($style=='default' && $format=='html') {
			$this->add('log/error',"Default style with no 'html' format.",ADD_ARRAY);
			return false;
		}
		
		if(!is_dir($d="style/$style/")) {
			$this->add('log/warning',"Style '$style' is not installed.",ADD_ARRAY);
			if($style=='default') {
				$this->add('log/error',"No default style.",ADD_ARRAY);
				return false;
			}
			$style = 'default';
			return $this->checkstyle($style,$format);
		}
		if(file_exists($fn="style/default/$format.php")) {
			$style = 'default';
			return $fn;
		}
		//echo "\t$style\t$format\t<br>\n";
		$format = 'html';
		return $this->checkstyle($style,$format);
	}
	
	function imports() {
		$f = $this->db->select('res_environ');
		if(!$f) return;
		foreach($f as $r) {
			switch($r['verb']) {
			case 'load':
				if(file_exists($fn = $r['basedir'].$r['file']))
					require_once $fn;
				break;
			}
		}
	}
	
	function getengine($engine) {
		if(!($enar = $this->db->select_first('res_engine',null,array('engine'=>"=$engine")))) {
			$enar = array('engine'=>$engine);
			$a = explode('/',$engine);
			$e = array_shift($a);
			if(is_dir($d = "mod/$e/")) {
				$enar['basedir'] = $d;
				$f = count($a)? array_shift($a): $e;
				$g = count($a)? implode('_',$a): $f;
				if(file_exists("$d$f.php")) {
					$enar['file'] = "$f.php";
					$enar['class'] = $g;
					$this->db->insert('res_engine',$enar);
				} if(file_exists("$d$e.php")) {
					if($g!=$f) $g = $f.'_'.$g;
					$enar['file'] = "$e.php";
					$enar['class'] = $g;
					$this->db->insert('res_engine',$enar);
				} elseif(file_exists($d."module.php")) {
					if($g!=$f) $g = $f.'_'.$g;
					$enar['file'] = "module.php";
					$enar['class'] = $g;
					$this->db->insert('res_engine',$enar);
				} else {
					$enar['file'] = null;
					$enar['class'] = $g;
				}
			} else {
				$enar['basedir'] = null;
				$enar['file'] = null;
				$enar['class'] = $e;
			}
		}
		if(isset($enar['basedir']) && isset($enar['file']))
			$enar['filename'] = $enar['basedir'].$enar['file'];
		$this->from_array('engine',$enar);
		return $enar;
	}
	
	function go() {
		#make content
		$engine = $this->get('line/engine','dispatch');
		$this->getengine($engine);
		if($fn=$this->get('engine/filename',false)) require $fn;
		if(class_exists($class = 'doc_'.$this->get('engine/class','raw')))
			$this->content = new $class($this);
		else $this->content = new nulldoc($this);
		
		#wrap a style
		$style = $this->get('style','default');
		$format = $this->get('format','html');
		$content = $this->get('content',null);
		if(($fn = $this->checkstyle($style,$format)) !== false) {
			$this->set('style',$style);
			$this->set('format',$format);
			$this->base = $base = require $fn;
			if($content) $base->make($content);
			else $base->blank();
		} else {
			if(isset($this->header)) {
				foreach($a=$this->header->as_array() as $b=>$c);
					header("$b: $c");
			} else {
				header('Content-type: text/plain;charset=utf8');
			}
			if(isset($this->content)) echo $this->content.chr(10).chr(10);
			print_r($this);
		}
	}
};

function il_add($key,$val,$how=ADD_ARRAY) { return Page::$first->add($key,$val,$how); }
function il_get($key,$def=null,$how=DEF_UNSET) { return Page::$first->get($key,$def,$how); }
function il_set($key,$val,$how=SET_REPLACE) { return Page::$first->set($key,$val,$how); }
function il_exists($key) { return Page::$first->exists($key); }
function il_empt($key) { return Page::$first->empt($key); }
function il_clear($key) { return Page::$first->clear($key); }

return new Page();
?>
