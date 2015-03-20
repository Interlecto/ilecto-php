<?php

function make_status(&$doc,$status,$alttitle=null,$altbody=null,$alttype='ilm',$sendstatus=true) {
	$r = db_select_first('base_status',null,array('id'=>"=$status"));
	if($r) {
		$doc->set('type',empty($r['type'])? 'ilm':$r['type']);
		$doc->set('title',$t = empty($r['text'])? sprintf('Status %03d',(int)$status): sprintf('%03d %s',(int)$status,$r['text']));
		$doc->set_content($r['record'],"[p $t]");
	} else {
		$doc->set('type','ilm');
		$doc->set('title',$t = sprintf('Status %03d',(int)$status));
		$doc->set('content',"[p $t]");
	}
	if(!empty($altbody)) {
		$doc->set('type',empty($alttype)?'ilm':$alttype);
		$doc->set('content',$altbody);
	}
	if(!empty($alttitle)) {
		$doc->set('title',$alttitle);
	}
	if($sendstatus) {
		$protocol = il_get('server/protocol','HTTP/1.0');
		header($h=sprintf('%s %03d %s',$protocol,(int)$status,$t));
		il_add('log/headers',$h,ADD_ARRAY);
	}
	$doc->add('content',"[x ".ilm_escape(htmlentities(print_r($doc,true)))."]");
}

require_once 'lib/doc.php';
class doc_status extends Doc {
	public $status;
	function __construct($page) {
		Doc::__construct($page);
		$linepar = $page->get('line/params');
		$this->status = $linepar[1];
	}
	function make() {
		make_status($this,$this->status,null,null,null,substr(il_get('request/uri'),0,8)!='/status/');
	}
};

?>
