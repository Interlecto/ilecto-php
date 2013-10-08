<?php

db_insert('res_engine',
	array(
		array('dispatch','mod/base/',null,'dispatch'),
		array('static','mod/base/','static.php','static'),
		array('status','mod/base/',null,'status'),
	),
	array('engine','basedir','file','class'),true);

db_insert('res_environ',
	array(
		array(1,'load','mod/base/','status.php'),
		array(2,'load','mod/base/','dispatch.php'),
		array(3,'load','mod/base/','ilm.php'),
	),
	array('id','verb','basedir','file'),true);

//db_query('DROP TABLE IF EXISTS `base_content`');
db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `base_text` (
	`idx` BIGINT(20),
	`lang` CHAR(2),
	`content` TEXT DEFAULT NULL,
	PRIMARY KEY(`idx`,`lang`),
	KEY `idx`(`idx`)
);
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `base_content` (
	`key` CHAR(128) PRIMARY KEY,
	`title` VARCHAR(255),
	`type` CHAR(12) DEFAULT 'ilm',
	`record` BIGINT(20) NOT NULL DEFAULT 0,
	`as` ENUM('auto','file','dir') DEFAULT 'auto'
);
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `base_titles` (
	`idx` BIGINT(20),
	`lang` CHAR(2),
	`title` TEXT DEFAULT NULL,
	PRIMARY KEY(`idx`,`lang`),
	KEY `idx`(`idx`)
);
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `base_status` (
	`id` INT(3) PRIMARY KEY,
	`text` CHAR(48),
	`type` CHAR(12) DEFAULT 'ilm',
	`record` BIGINT(20) NOT NULL DEFAULT 0
);
QUERY
);

db_insert( 'base_status',
	array(
		array(100,'Continue'),
		array(101,'Switching Protocols'),
		array(200,'OK'),
		array(201,'Created'),
		array(202,'Accepted'),
		array(203,'Non-Authoritative Information'),
		array(204,'No Content'),
		array(205,'Reset Content'),
		array(206,'Partial Content'),
		array(300,'Multiple Choices'),
		array(301,'Moved Permanently'),
		array(302,'Found'),
		array(303,'See Other'),
		array(304,'Not Modified'),
		array(305,'Use Proxy'),
		array(306,'(Unused)'),
		array(307,'Temporary Redirect'),
		array(400,'Bad Request'),
		array(401,'Unauthorized'),
		array(402,'Payment Required'),
		array(403,'Forbidden'),
		array(404,'Not Found'),
		array(405,'Method Not Allowed'),
		array(406,'Not Acceptable'),
		array(407,'Proxy Authentication Required'),
		array(408,'Request Timeout'),
		array(409,'Conflict'),
		array(410,'Gone'),
		array(411,'Length Required'),
		array(412,'Precondition Failed'),
		array(413,'Request Entity Too Large'),
		array(414,'Request-URI Too Long'),
		array(415,'Unsupported Media Type'),
		array(416,'Requested Range Not Satisfiable'),
		array(417,'Expectation Failed'),
		array(500,'Internal Server Error'),
		array(501,'Not Implemented'),
		array(502,'Bad Gateway'),
		array(503,'Service Unavailable'),
		array(504,'Gateway Timeout'),
		array(505,'HTTP Version Not Supported'),
	),
	array('id','text'),true);

db_insert( 'base_content',
	array(
		array('index','Main Page','ilm',1000,'auto'),
	),
	array('key','title','type','record','as'),true);

db_insert( 'base_text',
	array(
		array(1000,'en','[p Main Page.]'),
	),
	array('index','lang','content'),true);

?>
