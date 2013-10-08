<?php
require_once 'lib/doc.php';

class doc_static extends Doc {
	function make() {
		$lpar = $this->page->get('line/params');
		$this->set('pars/arr',$lpar);
		$lang = substr($lpar[0].$lpar[4].$lpar[6],0,2);
		$this->set('pars/lang',$lang);
		$lpar12 = empty($lpar[2])? $lpar[1]: $lpar[1].'/'.$lpar[2];
		$keys = array( trim($lpar12.'/'.$lpar[3],'/') );
		if(empty($lpar[3]))
			$keys[] = trim($lpar12.'/index','/');
		else
			$keys[] = trim($lpar12.'/'.$lpar[3].'/index','/');
		$this->set('pars/key',$keys);
		$rcon = db_select('base_content',null,array('key'=>$keys));
		if(!$rcon) {
			if($this->page->db->errno == 1146)
				require "mod/base/install.php";
			make_status($this,404);
		} else {
			$this->set('type',$rcon[0]['type']);
			$this->set_content($rcon[0]['record']);
			$this->set('title',$rcon[0]['title']);
		}
		$this->add('content','[x This = Attributer'.chr(10).ilm_escape($this->print_r(chr(9))).']');
		$this->add('content','[x Page = Attributer'.chr(10).ilm_escape($this->page->print_r(chr(9))).']');
	}
}

?>
