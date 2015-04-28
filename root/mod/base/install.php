<?php

db_insert($dp.'res_engine',
	array(
		array('dispatch','mod/base/',null,'dispatch'),
		array('static','mod/base/','static.php','static'),
		array('status','mod/base/',null,'status'),
	),
	array('engine','basedir','file','class'),true);

db_insert($dp.'res_environ',
	array(
		array(1,'load','mod/base/','status.php'),
		array(2,'load','mod/base/','dispatch.php'),
		array(3,'load','mod/ilm/','ilm.php'),
	),
	array('id','verb','basedir','file'),true);

//db_query('DROP TABLE IF EXISTS `{$dp}base_content`');
db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `{$dp}base_text` (
	`idx` BIGINT(20),
	`lang` CHAR(2),
	`content` TEXT DEFAULT NULL,
	PRIMARY KEY(`idx`,`lang`),
	KEY `idx`(`idx`)
);
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `{$dp}base_content` (
	`key` CHAR(128) PRIMARY KEY,
	`title` VARCHAR(255),
	`type` CHAR(12) DEFAULT 'ilm',
	`record` BIGINT(20) NOT NULL DEFAULT 0,
	`as` ENUM('auto','file','dir') DEFAULT 'auto'
);
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `{$dp}base_titles` (
	`idx` BIGINT(20),
	`lang` CHAR(2),
	`title` TEXT DEFAULT NULL,
	PRIMARY KEY(`idx`,`lang`),
	KEY `idx`(`idx`)
);
QUERY
);


db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `{$dp}base_object`(
	`key` CHAR(128) PRIMARY KEY,
	`class` CHAR(12) DEFAULT 'area',
	`type` CHAR(12) DEFAULT 'ilm',
	`record` BIGINT(20) NOT NULL DEFAULT 0
);
QUERY
);

db_insert( $dp.'base_object',
	array(
		array('mainnav','menu','auto',60000),
		array('syblings','menu','auto',60001),
		array('children','menu','auto',60002),
		array('breadcrumbs','menu','auto',60003),
		array('search','applet','ilm',60004),
		array('login','applet','ilm',60005),
		array('copyright','area','ilm',60006),
	),
	array('key','class','type','record'),true);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `{$dp}base_status` (
	`id` INT(3) PRIMARY KEY,
	`text` CHAR(48),
	`type` CHAR(12) DEFAULT 'ilm',
	`record` BIGINT(20) NOT NULL DEFAULT 0
);
QUERY
);

db_insert( $dp.'base_status',
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

db_insert( $dp.'base_content',
	array(
		array('index','Main Page','ilm',1000,'auto'),
	),
	array('key','title','type','record','as'),true);

db_insert( $dp.'base_text',
	array(
		array(1000,'en','[p Main Page.]'),
		array(60004,'en','[nav:#search{@title:o:Search}[f:get#s-box!search.cgi[f:text#q{@placeholder:Your search}][f:submit.ui __1F50E]]]'),
		array(60005,'en','{if:page:user}[f#u-box{text:Welcome} {page:user}[f:submit#logout{text:logout}]]{else}[f#u-box[f:text#username{text:Username}][f:password#passwd{text:Contraseña}]{fi}'),
		array(60006,'en','[b.cs#footer-copy[p[img:span.l!http://www.gnu.org/copyleft/fdl.html {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60][img:span.r!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60]{text:This page is double licensed as} [!http://www.gnu.org/copyleft/fdl.html {@rel;license}{text:GNU Free Documentation License}] {text:and} [!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}{text:Creative Commons Attribution-Noncommertial-Share Alike}]. {text:You can use any or both of this licenses}.][{text:For more info, please go to the} [!on/copy] {text:notice}.]'),
	),
	array('idx','lang','content'),true);

?>
