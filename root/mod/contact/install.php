<?php

db_insert("{$dp}res_engine",
	array(
		array('contact','mod/contact/','contact.php','contact'),
	),
	array('engine','basedir','file','class'),true);

db_insert("{$dp}res_case",
	array(
		array(12,'(?:([a-z]{2})/)?contacto?(?:\\.([a-z]{2}))?(?:\\.(\\w+!?))?(?:\\.([a-z]{2}))?(?:|/([-\\w]*\\w)?(?:@([a-z][-\\w]*\\.[-.\\w]+\\w))?)',2,'contact','0,1,3','2'),
	),
	array('id','case','priority','engine','langidx','formidx'),true);

db_insert("{$dp}base_text",
	array(
		array(15,'en','[f:{@style:horizontal}[f:set{@title:Personal info}[f:text#name{@label:Full name:}{@placeholder:Write your full name}][f:email#email{@label:Email:}{@placeholder:Enter your email address}]][f:set{@title:Message}[f:text#subject{@label:Subject:}{@placeholder:Provide a subject}][f:t#msg{@placeholder:Write your message}]][f:group[f:submit#send Send][f:reset]]]'),
		array(16,'en','[f:{@style:horizontal}[p: Your message has been sent.][b:pre {page:post:msg}]'),
		array(15,'es','[f:{@style:horizontal}[f:set{@title:Información personal}[f:text#name{@label:Nombre:}{@placeholder:Escriba su nombre completo}][f:email#email{@label:Correo electrónico:}{@placeholder:Su dirección de correo electrónico}]][f:set{@title:Message}[f:text#subject{@label:Asunto:}{@placeholder:Indique un motivo}][f:t#msg{@placeholder:Escriba su mensaje}]][f:group[f:submit#send Enviar][f:reset]]]'),
		array(16,'es','[f:{@style:horizontal}[p: Your message has been sent.][b:pre {page:post:msg}]'),
	),
	array('idx','lang','content'),true);

db_insert("{$dp}base_titles",
	array(
		array(15,'en','Contact;Contact %s'),
		array(16,'en','Your message was sent.'),
		array(15,'es','Contacto;Contacto con %s'),
		array(16,'es','Su mensaje fue enviado'),
	),
	array('idx','lang','title'),true);

?>
