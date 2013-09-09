<?php

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `user` (
  `id` char(24) CHARACTER SET ascii NOT NULL,
  `name` char(56) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `hash` char(32) CHARACTER SET ascii DEFAULT NULL,
  `avatar` int(20) unsigned DEFAULT NULL,
  `banner` int(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `avatar` (`avatar`),
  KEY `banner` (`banner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
QUERY
);
db_insert('user',
	array(
		array('super', 'Super User', '6ae5f59efb0b7fc0f2676e4d7fba6390'),
	),
	array('id', 'name', 'hash'),true);
	
db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `user_data` (
  `user` char(24) CHARACTER SET ascii NOT NULL,
  `param` char(16) CHARACTER SET ascii NOT NULL,
  `param_idx` int(2) NOT NULL DEFAULT '0',
  `value` char(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`user`,`param`,`param_idx`),
  KEY `user` (`user`),
  KEY `params` (`param`, `param_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `user_group` (
  `user` char(24) CHARACTER SET ascii NOT NULL,
  `group` char(24) CHARACTER SET ascii NOT NULL,
  `role` int(1) NOT NULL,
  PRIMARY KEY (`user`,`group`),
  KEY `role` (`role`),
  KEY `group` (`group`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
QUERY
);
db_insert('user_group',
	array(
		array('super', 'this_site', 9),
	),
	array('user', 'group', 'role'),true);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(1) NOT NULL,
  `role` char(12) CHARACTER SET ascii NOT NULL,
  `description` char(48) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
QUERY
);
db_insert('user_role',
	array(
		array(0, 'guest', 'visitante'),
		array(1, 'bot', 'usuario automatico'),
		array(2, 'user', 'usuario'),
		array(4, 'mailbox', 'contacto'),
		array(5, 'editor', 'editor'),
		array(6, 'publisher', 'publicador'),
		array(7, 'admin', 'administrador'),
		array(9, 'superuser', 'superusuario')
	),
	array('id', 'role', 'description'),true);


db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `avatar` (
  `idx` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `basedir` char(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `tiny` char(40) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `small` char(40) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `big` char(40) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `full` char(40) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `banner` (
  `idx` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `basedir` char(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `small` char(24) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `big` char(24) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `full` char(24) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idx` (`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
QUERY
);

db_query(<<<QUERY
CREATE TABLE IF NOT EXISTS `group` (
  `id` char(24) CHARACTER SET ascii NOT NULL,
  `name` char(56) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  `avatar` int(20) unsigned DEFAULT NULL,
  `banner` int(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
QUERY
);
db_insert('group',
	array(
		array('this_site', 'This Site'),
	),
	array('id', 'name'),true);


db_insert('res_case',
	array(
		array(5,'(?:([a-z]{2})/)?(user|usuario)(?:/([-\\w]*\\w)?(?:/([-\\w]*\\w)?(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)?)?',2,'user','0,4,6','5'),
		array(6,'(?:([a-z]{2})/)?login(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:|/([-\\w]*\\w)?(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)',2,'login','0,1,3,5,7','2,6'),
		array(7,'(?:([a-z]{2})/)?(users?|usuarios?)(?:|/|(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)',3,'users','0,2,4','3'),
		array(8,'(?:([a-z]{2})/)?login(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:/(logout|out)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)',3,'login/out','0,1,3,5,7','2,6'),
		array(9,'(?:([a-z]{2})/)?logout(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?/?',3,'login/out','0,1,3','2'),
		array(10,'(?:([a-z]{2})/)?login(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:/(reg(?:ist(?:er|ro))?)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)',3,'login/reg','0,1,3,5,7','2,6'),
		array(11,'(?:([a-z]{2})/)?(?:register|registro)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?/?',3,'login/reg','0,1,3','2'),
	),
	array('id','case','priority','engine','langidx','formidx'),true);

#db_update('res_case',array('case'=>'(?:([a-z]{2})/)?(users?|usuarios?)(?:|/|(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)'),array('id'=>'=7'));
#db_update('res_case',array('case'=>'(?:([a-z]{2})/)?login(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:/(logout|out)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)'),array('id'=>'=8'));
#db_update('res_case',array('case'=>'(?:([a-z]{2})/)?login(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:/(reg(?:ist(?:er|ro))?)(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?)'),array('id'=>'=10'));

db_insert('res_engine',
	array(
		array('user','mod/user/','user.php','user'),
		array('users','mod/user/','user.php','users'),
		array('login','mod/user/','login.php','login'),
		array('login/out','mod/user/',null,'logout'),
		array('login/reg','mod/user/',null,'register'),
	),
	array('engine','basedir','file','class'),true);

db_insert('res_environ',
	array(
		array(4,'load','mod/user/','login.php'),
	),
	array('id','verb','basedir','file'),true);

#db_update('res_engine',array('file'=>'user.php'),array('file'=>"#",'basedir'=>'mod/user/'));
#db_update('res_engine',array('file'=>null),array('file'=>"=login.php"));

db_insert('base_text',
	array(
		array(10,'en','[@F[@P[@L#user User Name][@I#user]][@P[@L#passwd Password][@W#passwd]][@P[@G][@R]]]'),
		array(11,'en','[@F]'),
		array(12,'en','[@F[@P[@L#name Full Name][@I#name]][@P[@L#user User Name][@I#user]][@P[@L#passwd Password][@W#passwd]][@P[@L#passwd2 Repeat password][@W#passwd2]][@P[@L#email Email][@I#email]][@P[@G][@R]]]'),
		array(10,'es','[@F[@P[@L#user Nombre de usuario][@I#user]][@P[@L#passwd Contraseña][@W#passwd]][@P[@G][@R]]]'),
		array(11,'es','[@F]'),
		array(12,'es','[@F[@P[@L#name Nombre completo][@I#name]][@P[@L#user Nombre de usuario][@I#user]][@P[@L#passwd Contraseña][@W#passwd]][@P[@L#passwd2 Repetir contraseña][@W#passwd2]][@P[@L#email Correo electrónico][@I#email]][@P[@G][@R]]]'),
	),
	array('idx','lang','content'),true);

?>
