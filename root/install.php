<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

header('Content-type: text/plain;charset=utf8');
require_once 'lib/db.php';
require_once 'site_def.php';
$db = new db(
	isset($il_data_server)?$il_data_server:'localhost',
	isset($il_data_user)?$il_data_user:'root',
	isset($il_data_password)?$il_data_password:''
);
$database = db_var(isset($il_data_database)?$il_data_database:'interlecto');
$sitename = db_val(isset($il_data_database)?$il_data_database:'Interlecto');
function il_add($a,$text) { echo trim($text).chr(10); }

$db->query("CREATE DATABASE IF NOT EXISTS $database;");
$db->query("USE $database;");
$db->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `gen_object`(
	`id` SERIAL PRIMARY KEY,
	`name` CHAR(48) CHARACTER SET ascii,
	UNIQUE `name`(`name`),
	`common` CHAR(64) DEFAULT NULL,
	`parent` BIGINT(20) DEFAULT 1,
	KEY `parent` (`parent`)
);
QUERY
);
$db->query(<<<QUERY
INSERT INTO `gen_object`(`id`,`name`,`common`,`parent`)
VALUES
(1,'this_site',$sitename,NULL),
(2,'super','Super User',1)
ON DUPLICATE KEY UPDATE `id`=`id`;
QUERY
);
//$db->query('DROP TABLE IF EXISTS `gen_param`;');
$db->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `gen_param`(
	`object` BIGINT(20),
	`param` CHAR(24) CHARACTER SET ascii,
	`idx` INT(3) NOT NULL DEFAULT 1,
	`value` CHAR(64) DEFAULT NULL,
	PRIMARY KEY(`object`,`param`,`idx`),
	KEY `object` (`object`),
	KEY `param_i` (`param`,`idx`),
	KEY `param` (`param`)
);
QUERY
);
//$db->query('DROP TABLE IF EXISTS `gen_user`;');
$db->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `gen_user`(
	`id` BIGINT(20) PRIMARY KEY,
	`hash` CHAR(33) CHARACTER SET ascii,
	`hashed` TINYINT(1) DEFAULT 1
);
QUERY
);
$db->query(<<<QUERY
INSERT INTO `gen_user`(`id`,`hash`,`hashed`)
VALUES
(2,'1n73r-l3c70',0)
ON DUPLICATE KEY UPDATE `id`=`id`;
QUERY
);
$db->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `gen_param_desc`(
	`param` CHAR(24) CHARACTER SET ascii,
	`idx` INT(3) NOT NULL DEFAULT 1,
	`description` CHAR(64) DEFAULT NULL,
	PRIMARY KEY(`param`,`idx`),
	KEY `param` (`param`)
);
QUERY
);
//$db->query('DROP TABLE IF EXISTS `res_case`');
$db->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `res_case`(
	`id` SERIAL PRIMARY KEY,
	`case` CHAR(255),
	UNIQUE `case`(`case`),
	`priority` TINYINT(2),
	`engine` CHAR(48),
	`langidx` CHAR(24) CHARACTER SET ascii,
	`formidx` CHAR(24) CHARACTER SET ascii,
	KEY `engine`(`engine`)
);
QUERY
);
$db->query(<<<QUERY
INSERT INTO `res_case`(`id`,`case`,`priority`,`engine`,`langidx`,`formidx`)
VALUES
(1,'(.*?)',0,'dispatch',NULL,NULL),
(2,'(?:([a-z]{2})/)?()()([-\\\\w]*\\\\w)?(?:\\\\.([a-z]{2}))?(?:\\\\.(\\\\w+!?))?(?:\\\\.([a-z]{2}))?',1,'static','0,4,6','5'),
(3,'(?:([a-z]{2})/)?([-\\\\w]*\\\\w)?(?:/([-\\\\w/]*\\\\w))?(?:/([-\\\\w]*\\\\w)?(?:\\\\.([a-z]{2}))?(?:\\\\.(\\\\w+!?))?(?:\\\\.([a-z]{2}))?)',1,'static','0,4,6','5'),
(4,'(?:([a-z]{2})/)?status/(\\\\d{3})(?:\\\\.([a-z]{2}))?(?:\\\\.(\\\\w+!?))?(?:\\\\.([a-z]{2}))?',90,'status','0,2,4','3')
ON DUPLICATE KEY UPDATE `id`=`id`;
QUERY
);
//$db->query("UPDATE `res_case` SET `case`='(.*?)' WHERE `id`=1;");
//$db->query("UPDATE `res_case` SET `case`='(?:([a-z]{2})/)?()()([-\\\\w]*\\\\w)?(?:\\\\.([a-z]{2}))?(?:\\\\.(\\\\w+!?))?(?:\\\\.([a-z]{2}))?', `priority`=2 WHERE `id`=2;");
//$db->query("UPDATE `res_case` SET `case`='(?:([a-z]{2})/)?([-\\\\w]*\\\\w)?(?:/([-\\\\w/]*\\\\w))?(?:/([-\\\\w]*\\\\w)?(?:\\\\.([a-z]{2}))?(?:\\\\.(\\\\w+!?))?(?:\\\\.([a-z]{2}))?)' WHERE `id`=3;");
//$db->query("UPDATE `res_case` SET `case`='(?:([a-z]{2})/)?status/(\\\\d{3})(?:\\\\.([a-z]{2}))?(?:\\\\.(\\\\w+!?))?(?:\\\\.([a-z]{2}))?' WHERE `id`=4;");
$db->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `res_engine`(
	`engine` CHAR(48) PRIMARY KEY,
	`basedir` CHAR(48),
	`file` CHAR(48),
	`class` CHAR(48),
	`type` CHAR(12) DEFAULT 'php'
);
QUERY
);
$db->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `res_environ`(
	`id` SERIAL PRIMARY KEY,
	`verb` CHAR(48),
	`basedir` CHAR(48),
	`file` CHAR(48)
);
QUERY
);

if(file_exists($fn = 'mod/base/install.php')) require $fn;
if(file_exists($fn = 'mod/user/install.php')) require $fn;

?>
