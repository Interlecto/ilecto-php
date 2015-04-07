<?php

require_once "lib/doc.php";
class doc_edit extends Doc {
	function prepare_check() {
		$ln = $this->dotlang();
		$engine = il_get('engine/class','create');
		$docname = "$engine$ln.cgi";
		$id = il_get('getv/id',0);
		$rl = il_get('getv/lang','');
		$status = 200;
		if(!empty($id)) {
			$dockey = db_select_one('base_content','key',array('record'=>"=$id"));
			if(empty($dockey)) {
				$r = db_select('base_text','lang',array('idx'=>"=$id"));
				if(empty($r)) $status = 404;
				$q = "?id=$id";
				if(!empty($rl)) $q.= urlencode("&lang=$rl");
				check_or_redirect("_/$docname",null,$q);
			} elseif(($n=strrpos($dockey,'/'))!==false) {
				$path = substr($dockey,0,$n+1);
				$doc = substr($dockey,$n+1);
				$q = "?doc=$doc";
				if(!empty($rl)) $q.= urlencode("&lang=$rl");
				check_or_redirect("$path$docname",null,$q);
			} else {
				$q = "?doc=$dockey";
				if(!empty($rl)) $q.= urlencode("&lang=$rl");
				check_or_redirect("$docname",null,$q);
			}
			il_set('edit/ns','!');
			il_set('edit/id',$id);
			il_set('edit/lang',$rl?$rl:il_get('lang','en'));
		} else {
			$path = il_get('line/params/1','');
			if(substr($path,-6)=='index/')
				$path = substr($path,0,-6);
			$doc = il_get('getv/doc','index');
			$dockey = $path.$doc;
			if(($n=strrpos($dockey,'/'))!==false) {
				$path = substr($dockey,0,$n+1);
				$doc = substr($dockey,$n+1);
			} else {
				$path = '';
				$doc = $dockey;
			}
			$id = db_select_one('base_content','record',array('key'=>"=$dockey"));
			if(empty($id)) $status=404;
			$q = "?doc=$doc";
			if(!empty($rl)) $q.= urlencode("&lang=$rl");
			check_or_redirect("$path$docname",null,$q);
			il_set('edit/ns',$path);
			il_set('edit/doc',$doc);
			il_set('edit/id',$id);
			il_set('edit/lang',$rl?$rl:il_get('lang','en'));
			il_set('edit/status',$status);
		}
		if(il_exists('post/save')||il_exists('post/finish')) $this->commit();
		if($engine=='edit' && $status!=200) {
			make_status($this,$status);
		}
	}
	
	function make() {
		$id = (int)il_get('edit/id',0);
		$dlang = il_get('edit/lang','en');
		$ilang = il_get('lang','en');

		$this->set('type','ilm');
		$this->set_content(99,'[f:{@style:horizontal}[f:text#url{@label:URL}{@bool:disabled}{:uri}][f:text#title{@label:Title}{:title}][f:text#lang{@label:Language}{:lang}][f:s#type{@label:Type}[f:o"ILM"][f:o"HTML"]][f:t#code{@label:Texto}{:content}][f:group[f:submit#finish Finish][f:submit#save Save][f:reset]]]');
		$title = $this->get('title','Edit “{:title}”');
		$content = $this->get('content');
		
		ILMmath::set('lang',$dlang);
		
		$ns = il_get('edit/ns','');
		if($ns=='!') {
			ILMmath::set('uri',"[scripted ( $id )]");
		} else {
			$doc = il_get('edit/doc','index');
			if($dlang!=$ilang)
				$doc = "$doc.$dlang.html";
			else
				$doc = $doc=='index'? '': "$doc.html";
			$uri = "/$ns$doc";
			ILMmath::set('uri',$uri);
		}

		$s = db_select_key('base_titles','lang',array('idx'=>"=$id"));
		if(empty($s)) {
			$t = db_select_one('base_content','title',array('record'=>"=$id"));
			ILMmath::set('title',$t);
		}
		elseif(isset($s[$dlang]))
			ILMmath::set('title',$s[$dlang]['title']);
		elseif(isset($s[$ilang]))
			ILMmath::set('title',$s[$ilang]['title']);
		else {
			$sv = array_values($s);
			ILMmath::set('title',$sv[0]['title']);
		}

		$r = db_select_key('base_text','lang',array('idx'=>"=$id"));
		if(empty($r)) {
			ILMmath::set('content','');
		}
		elseif(isset($r[$dlang]))
			ILMmath::set('content',$r[$dlang]['content']);
		elseif(isset($r[$ilang]))
			ILMmath::set('content',$r[$ilang]['content']);
		else {
			$rv = array_values($r);
			ILMmath::set('content',$rv[0]['content']);
		}

		ILMmath::set('setilm','');
		ILMmath::set('sethtml','');
		$tobj = ILM::read($title);
		$title = ILM::totext($tobj);

		$this->set('title',$title);
		$this->set('content',$content);
	}

	function commit() {
		$id = (int)il_get('edit/id',0);
		$dlang = il_get('post/lang','');
		$ilang = il_get('lang','en');
		$ns = il_get('edit/ns','');
		if($ns=='!') {
			$uri='/';
			$key = null;
		} else {
			$doc = il_get('edit/doc','index');
			$key = $ns.$doc;
			if($dlang!=$ilang)
				$doc = "$doc.$dlang.html";
			else
				$doc = $doc=='index'? '': "$doc.html";
			$uri = "/$ns$doc";
		}
		
		$title = il_get('post/title','');
		$type = strtolower(il_get('post/type',''));

		if(!empty($title)) {
			$tit = db_select_one('base_content','title',array('record'=>"=$id"));
			if($tit===false && !is_null($key)) {
				db_insert('base_content',array(
					array(
						$key,$title,$type,$id,'auto'
					)),
					array(
						'key','title','type','record','as'
					));
			} elseif(empty($tit)) {
				$t = db_select_key('base_titles','lang',array('idx'=>"=$id"));
				if(isset($t[$dlang])) {
					$tit = $t[$dlang]['title'];
					if($tit != $title)
						db_update('base_titles',array('title'=>$title),array('idx'=>"=$id",'lang'=>"=$dlang"));
				} else {
					db_insert('base_titles',array(array($id,$dlang,$title)),array('idx','lang','title'));
				}
			} elseif($tit!=$title) {
				db_update('base_content',array('title'=>$title),array('record'=>$id));
			}
		}
		$rtype = db_select_one('base_content','type',array('record'=>"=$id"));
		if(!empty($rtype) && $rtype!=$type)
			db_update('base_content',array('type'=>$type),array('record'=>$id));

		$code = il_get('post/code','');
		if(!empty($code)) {
			$r = db_select_key('base_text','lang',array('idx'=>"=$id"));
			if(isset($r[$dlang])) {
				$cont = $t[$dlang]['content'];
				if($cont != $code)
					db_update('base_text',array('content'=>$code),array('idx'=>"=$id",'lang'=>"=$dlang"));
			} else {
				db_insert('base_text',array(array($id,$dlang,$code)),array('idx','lang','content'));
			}
		}

		if(il_exists('post/finish')) redirect($uri);
		#print_r(Page::$first->queries);
	}
};

?>
