<?php

require_once "lib/doc.php";
class doc_contact extends Doc {
	function prepare_check() {
		$ln = $this->dotlang();
		$docname = "contact$ln.cgi";
		$domain = strtolower(il_getorset('site/domain','interlecto.net'));
		$touser = il_get('line/params/4');
		$todomain = strtolower(il_get('line/params/5',$domain,DEF_EMPTY));
		if(empty($touser)) {
			check_or_redirect($docname);
		} else {
			il_set('mailto/user',$touser);
			il_set('mailto/domain',$todomain);
			if($todomain==$domain) {
				check_or_redirect("$docname/$touser");
				$toname = db_select_one('user','name',array('id'=>"=$touser"));
				il_set('mailto/name',$toname);
			} else
				check_or_redirect("$docname/$touser@$todomain");
		}
	}
	
	function makeform() {
		$user = il_get('mailto/user');
		$name = il_get('mailto/name');
		$domain = il_get('mailto/domain');
		$this->set_content(15);
		$title = explode(';',$this->get('title','Contact;Contact %s'));
		$title = empty($user)? $title[0]:
			sprintf($title[1],empty($name)?"$user@$domain":$name);
		$this->set('title',$title);
		il_set('title',$title);
	}
	
	function makesent() {
		$this->set_content(16);
		$this->getorset('title','Message sent');
	}
	
	function send_email() {
		return true;
	}
	
	function make() {
		$this->set('type','ilm');
		$send = il_get('post/send','');
		if(!empty($send))
			return $this->send_email()?
				$this->makesent():
				$this->makeform();
		$this->makeform();
	}
};

?>
