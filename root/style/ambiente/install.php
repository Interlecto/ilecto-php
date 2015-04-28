<?php

if(empty($dbx)) {
	$dbf = db_select_first('base_content','record',true,'-record');
	$n = max((int)$dbf['record'],60100);
	$dbx = $n;
} else {
	$n = (int)$dbx;
}

db_insert('base_content',array(
	array('@ambiente','Ambiente','html',$n++,'auto'),
	array('@area:header','header','ilm',$n++,'file'),
	array('@area:topbar','top bar','ilm',$n++,'file'),
	array('@area:sidebar','side bar','ilm',$n++,'file'),
	array('@area:footer','footer','ilm',$n++,'file'),
	array('@menu:copycont','Copy/Contact','inline+x2014',$n++,'auto'),
	array('@area:footer-copy','Copyleft','ilm',$n++,'auto'),
	array('@area:footer-validate','Validation','ilm',$n++,'auto'),
	array('@menu:mainnav','Main navigation','ilm',$n++,'auto'),
	array('@menu:syblings','Syblings','php',$n++,'auto'),
	array('@menu:children','Children','php',$n++,'auto'),
	array('@applet:search','Search','html',$n++,'auto'),
	array('@applet:googlead','Advertizing','html',$n++,'auto'),
	array('@applet:donations','Donations','html',$n++,'auto'),
	array('@applet:visitors','Visitors','html',$n++,'auto'),
	),
	array('key','title','type','record','as'));

db_insert('base_content',array(
	array('on/home','Home','red(index)',$n++,'auto'),
	array('on/cont','Contact','html',$n++,'auto'),
	array('on/copy','Copyright','html',$n++,'auto'),
	array('on/guest','Guestbook','html',$n++,'auto'),
	array('on/search','Search','html',$n++,'auto'),
	),
	array('key','title','type','record','as'));

$n = $dbx+4;
db_insert('base_text',array(
	array(++$n,'en','on/home,on/cont,on/copy,on/guest,on/search'),
	array(++$n,'en','[b.cs#footer-copy[p[img:span.l!http://www.gnu.org/copyleft/fdl.html {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60][img:span.r!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60]{text:This page is double licensed as} [!http://www.gnu.org/copyleft/fdl.html {@rel;license}{text:GNU Free Documentation License}] {text:and} [!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}{text:Creative Commons Attribution-Noncommertial-Share Alike}]. {text:You can use any or both of this licenses}.][{text:For more info, please go to the} [!on/copy] {text:notice}.]'),
	array(  $n,'es','[b.cs#footer-copy[p[img:span.l!http://www.gnu.org/copyleft/fdl.html {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60][img:span.r!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60]Esta página esta doblemente licenciada como [!http://www.gnu.org/copyleft/fdl.html {@rel;license}Licencia de documentación libre de GBU] y [!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}Licencia Creative Commons Atribución, No comercial, Compártase igual]. You can use any or both of this licenses.][Para mayor información, ver la nota de [!on/copy].]'),
	array(++$n,'en','[b.cs#footer-validate[p[img:span!http://www.gnu.org/copyleft/fdl.html {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60][img:span!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}{@src:/images/gnu-fdl.png}{@width:88px}{@height:31px}_60]{text:This page is double licensed as} [!http://www.gnu.org/copyleft/fdl.html {@rel;license}{text:GNU Free Documentation License}] {text:and} [!http://creativecommons.org/licenses/by-nc-sa/3.0/ {@rel:license}{text:Creative Commons Attribution-Noncommertial-Share Alike}]. {text:You can use any or both of this licenses}.][{text:For more info, please go to the} [!on/copy] {text:notice}.]'),
	),
	array('idx','lang','content'));

?>
