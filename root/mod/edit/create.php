<?php

require_once "mod/edit/edit.php";
class doc_create extends doc_edit {
	function prepare_check() {
		doc_edit::prepare_check();
		$status = il_get('edit/status');
		if((int)$status==200) {
			$e = il_get('line/clean');
			$q = il_get('line/query');
			$e = preg_replace('{\bcreate\.}','edit.',$e);
			check_or_redirect($e,null,$q?"?$q":'');
		}
		il_set('edit/estado',$status);
	}
};

?>
