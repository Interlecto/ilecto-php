<?php

function get_user() {
	if($u = il_get('php/auth_user',false)) {
		$p = il_get('php/auth_pw','');
		if(check_user($u,$p)) {
			set_user($u);
			return $u;
		} else {
			clear_user();
			return null;
		}
	}
	if($u = il_get('req/user',false)) {
		$p = il_get('req/passwd','');
		if(check_user($u,$p)) {
			set_user($u);
			return $u;
		} else {
			clear_user();
			return null;
		}
	}
	if($u = il_get('session/user',false)) {
		set_user($u);
		return $u;
	}
	clear_user();
	return null;
}

function check_user($user,$passwd) {
	$md5 = md5("[$user:$passwd]");
	$r = db_select_first('user',array('id','name','avatar','banner'),array('id'=>"=$user",'hash'=>"=$md5"));
	if($r) {
		il_set('user/record',$r);
		return true;
	}
	return false;
}

function set_user($user) {
	il_set('user/id',$user);
	if(!($r = il_get('user/record',false))) {
		$r = db_select_first('user',array('id','name','avatar','banner'),array('id'=>"=$user"));
	}
	if($r) {
		il_set('user/name',$r['name']);
		il_set('user/avatar',$r['avatar']);
		il_set('user/banner',$r['banner']);
	}
	$d = db_select('user_data',null,array('user'=>"=$user"));
	if($d) foreach($d as $dr) {
		$k = 'user/'.$dr['param'].($dr['param_idx']?'_'.$dr['param_idx']:'');
		il_set($k,$dr['value']);
	}
	$g = db_select('user_group',null,array('user'=>"=$user"));
	if($g) foreach($g as $gr) {
		$k = $gr['group']=='this_site'? 'user/role': 'user/group/'.$gr['group'];
		il_set($k,$gr['role']);
	}
	$_SESSION['user'] = $user;
	$_SESSION['user_role'] = il_get('user/role',0);
	$_SESSION['user_name'] = il_get('user/name',$user);
}

function clear_user() {
	il_clear('user');
	unset($_SESSION['user']);
	unset($_SESSION['user_role']);
	unset($_SESSION['user_name']);
}

require_once "lib/doc.php";
class doc_login extends Doc {
	function make() {
		$this->set('type','ilm');
		$this->set_content(10);
		$lan = il_get('lang','en');
		check_or_redirect($lan=='en'?'login/':"login/login.$lan.cgi");
	}
};

class doc_logout extends Doc {
	function make() {
		$this->set('type','ilm');
		$this->set_content(11);
		$lan = il_get('lang','en');
		check_or_redirect($lan=='en'?'login/logout.cgi':"login/logout.$lan.cgi");
	}
};

class doc_register extends Doc {
	function make() {
		$this->set('type','ilm');
		$this->set_content(12);
		$lan = il_get('lang','en');
		check_or_redirect($lan=='en'?'login/register.cgi':"login/register.$lan.cgi");
	}
};

?>
