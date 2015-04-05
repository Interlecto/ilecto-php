<?php

db_insert("{$dp}res_engine",
	array(
		array('search','mod/search/',null,'search'),
	),
	array('engine','basedir','file','class'),true);

db_insert("{$dp}res_environ",
	array(
		array(6,'load','mod/search/','search.php'),
	),
	array('id','verb','basedir','file'),true);

db_insert("{$dp}res_case",
	array(
		array(13,'(?:([a-z]{2})/)?(search/buscar/busqueda)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:|/([-\\w]*\\w)?(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)',2,'search','0,1,3,5,7','2,6'),
	),
	array('id','case','priority','engine','langidx','formidx'),true);


?>
