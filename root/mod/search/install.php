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

db_insert("{$dp}base_text",
	array(
		array(13,'en','[f:get{@style:horizontal}[f:text#q{@label:Search terms:}{@placeholder:Your search}][f:group[f:submit Search][f:reset]]]'),
		array(14,'en','[p: Search results:][b:section {searchresult}]'),
		array(13,'es','[f:get{@style:horizontal}[f:text#q{@label:Términos de búsqueda:}{@placeholder:Su búsqueda}][f:group[f:submit Buscar][f:reset]]]'),
		array(14,'es','[p: Resultado de la búsqueda:][b:section {searchresult}]'),
	),
	array('idx','lang','content'),true);

db_insert("{$dp}base_titles",
	array(
		array(13,'en','Search'),
		array(14,'en','Results for "%s"'),
		array(13,'es','Búsqueda'),
		array(14,'es','Resultados de "%s"'),
	),
	array('idx','lang','title'),true);

?>
