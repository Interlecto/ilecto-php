<?php

db_insert("{$dp}res_engine",
	array(
		array('edit','mod/edit/',null,'edit'),
	),
	array('engine','basedir','file','class'),true);

db_insert("{$dp}res_environ",
	array(
		array(8,'load','mod/edit/','edit.php'),
	),
	array('id','verb','basedir','file'),true);

db_insert("{$dp}res_case",
	array(
		array(16,'(?:([a-z]{2})/)?(?:([-\\w]*\\w)/)?(edit(?:ar)?|edicion)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?',2,'edit','0,3,5','4'),
	),
	array('id','case','priority','engine','langidx','formidx'),true);


?>
