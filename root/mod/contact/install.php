<?php

db_insert("{$dp}res_engine",
	array(
		array('contact','mod/contact/',null,'contact'),
	),
	array('engine','basedir','file','class'),true);

db_insert("{$dp}res_environ",
	array(
		array(5,'load','mod/contact/','contact.php'),
	),
	array('id','verb','basedir','file'),true);

db_insert("{$dp}res_case",
	array(
		array(12,'(?:([a-z]{2})/)?contacto?(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:|/([-\\w]*\\w)?(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)',2,'contact','0,1,3,5,7','2,6'),
	),
	array('id','case','priority','engine','langidx','formidx'),true);


?>
