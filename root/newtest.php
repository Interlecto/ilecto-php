<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ILM Test</title>
<link rel="stylesheet" href="/style/default/css/style.css" type="text/css" />
<link rel="stylesheet" href="/style/default/css/layout.css" type="text/css" />
<link rel="icon" href="/images/interlecto.ico">
<style></style>
</head>
<?php

$ilmt = <<<ILM
[b:body
	[b:head
		[nav:p.o {text:Go to} [!#search{text:search}], [!#menu{text:menu}], [!#links{text:links}]]
		[p:h "The Page"]
	]
	[b:content
		[p:h "The Doc Title"]
		[b:section
			[p:h Lorem Ipsum]
			[p: Lorem ipsum dolor sit amet, consectetur adipiscing elit.
				Donec lacinia consectetur nisi, id aliquam leo tristique non.
				Cras ut ullamcorper nunc, sit amet ultricies tellus.
				Nunc maximus, felis vel i vehicula pretium, diam ante faucibus sapien, et fermentum ipsum sem in dui.
				Mauris eu lorem vitae libero fermentum scelerisque.]
		]
		[b:section.twocola{@title:Phrases}
			[p:bullet ¡_46_e1ct _u00e0_20__00006ca c__101rte!]
			[p:bullet {text:The quick brown fox jumps over the lazy dog}.]
			[p:bullet Allá exigí una libra de azúcar, dos de kiwis y un poco de arroz, jamón y güeñas que no tenía, leche, huevos y café.{@lang:es}]
			[p:bullet{@lang:de}Zwölf Boxkämpfer jagten Victor quer über den großen Sylter Deich.]
			[p:bullet{@lang:ei}Kæmi ný öxi hér ykist þjófum nú bæði víl og ádrepa]
			[p:bullet{@lang:he:rtl}עטלף אבק נס דרך מזגן שהתפוצץ כי חם]
			[p:bullet{@lang:ei}Kuba harpisto ŝajnis amuziĝi facilege ĉe via ĵaŭda ĥoro.]
			[p:bullet{@lang:sv}Flygande bäckasiner söka hwila på mjuka tuvor.]
		]
		[b:section.twocolb
			[p:h Lists]
			[b:section{@title:Simple lists}
				[p: Simple lists use the p namespace _22"[p:bullet"_22]
				[p:bullet uno]
				[p:disc dos]
				[p:circle tres]
				[p:disc cuatro]
				[p::guy cinco]]
			[b:section{@title:Bulleted lists}
				[p: Normal lists use the l namespace _22"[l:bullet"_22]
				[l:bullet uno]
				[l:disc dos]
				[l:circle tres]
				[l: cuatro][q o]]
			[b:section{@title:Icon lists}
				[l::flickr uno]
				[l::instagram dos]
				[l::facebook tres]]
			[b:section{@title:Ordered lists}
				[l:# uno]
				[l:# dos]
				[l:alpha tres]
				[l:alpha cuatro]]
			[b:section{@title:Structured lists}
				[l:# uno
					[l:# uno.uno]
					[l:* uno.dos]
				]
				[l:alpha dos]
				[l:alpha tres
					[l:circle tres.uno]
					[l:bullet tres.dos
						[l: tres.dos.uno]
						[l: tres.dos.dos]
					]
				]
				[l: cuatro]
			]
		]
		[b:section.twocola{@title:Tables}
			[t:fancy.cf
				[t:head
					[t:row
						[t:h]
						{for:col:1:5}[t:d Column {:col}]{next}
					]
				]
				{for:sec:1:3}[t:body
					[t:row[t:h§{:sec}][t:h{@colspan:5}]]
					{for:row:1:sec+1}[t:row
						[t:h §{:sec} row-{alpha:row}]
						{for:col:1:row}[t:d §{:sec} {alpha:row}{:col}]{next}
						[t:d.grayed{@colspan:(5-row)} left]
					]{next}
				]{next}
			{@title:Table test}]
		]
	]
	[b:aside
		[f:#search]
		[nav:menu#menu]
		[nav:menu#links]
		[b:#toc {auto:toc}]
	]
	[b:foot#footer
		{ui:copyleft} 2015, Interlecto
	]
]
ILM;

/**********************************************
 * ILM (ilm.php)
 * */
require_once "mod/ilm/ilm.php";

$i18n = array(
'Go to'=>'Ir a',
'search'=>'búsqueda',
'menu'=>'menú',
'links'=>'enlaces',
);

include_once 'lib/tidy.php';
$ob = ILM::read($ilmt);
echo ILM::tohtml($ob,null,true);

?>

</html>
