<?php

db_insert($dp.'res_environ',
	array(
		array(3,'load','mod/ilm/','ilm.php'),
	),
	array('id','verb','basedir','file'),true);

?>
