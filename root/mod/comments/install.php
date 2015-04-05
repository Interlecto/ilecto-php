<?php

db_insert("{$dp}res_engine",
	array(
		array('comment','mod/comments/','comment.php','comment'),
		array('comment/edit','mod/comments/','edit.php','edit_comment'),
		array('suggestion','mod/comments/','suggest.php','suggestion'),
		array('suggestion/edit','mod/comments/','edit.php','edit_suggestion'),
	),
	array('engine','basedir','file','class'),true);

db_insert("{$dp}res_environ",
	array(
		array(7,'load','mod/comments/','comment.php'),
	),
	array('id','verb','basedir','file'),true);

db_insert("{$dp}res_case",
	array(
		array(14,'(?:([a-z]{2})/)?(?:([-\\w]*\\w)/)?(comments?|comentar(?:ios?)?)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?',2,'comment/edit','0,3,5','4'),
		array(15,'(?:([a-z]{2})/)?(?:([-\\w]*\\w)/)?(suggest(?:ions?)?|suger(?:encias?|ir))(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?',2,'comment/edit','0,3,5','4'),
	),
	array('id','case','priority','engine','langidx','formidx'),true);


db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `{$dp}comments` (
	`comment` BIGINT(20),
	`document` BIGINT(20),
	`replyto` BIGINT(20),
	`scope` INT(1),
	PRIMARY KEY(`comment`)
);
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `{$dp}comment_type` (
	`scope` INT(1) PRIMARY KEY,
	`type` VARCHAR(255),
	PRIMARY KEY(`scope`)
);
QUERY
);

db_insert("{$dp}comment_type",
	array(
		array(1,'comment'),
		array(2,'suggestion'),
	),
	array('scope','type'),true);

?>
