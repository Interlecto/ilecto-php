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
	[b:head.ca#header
		[nav:p.o#ilinks {text:Go to} [!#search{text:search}], [!#menu{text:menu}], [!#links{text:links}]]
		{img:left:#logo:small.svg!/ Interlecto}
		[p:h#siteid "The Site"]
		[p:sub#sitetag{text:motto}]
	]
	[b:content#body
		[p:h#title "The Doc Title"]
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
				[p: Simple lists use the p namespace _22"[p:bullet...]"_22]
				[p:bullet uno]
				[p:disc dos]
				[p:circle tres]
				[p:disc cuatro]
				[p::guy cinco]]
			[b:section{@title:Bulleted lists}
				[p: Normal lists use the l namespace _22"[l:bullet...]"_22]
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
		[b:section.twocola{@title:Forms}
			[f:#gb!gb
				[f:set{@title:Personal Info}{@style:horizontal}
					[f:text#name{@label:Name:}"Prefilled name"]
					[f:password#passwd{@label:Password:}{@placeholder:Enter your password}]
					[f:email#email{@label:Email:}{@placeholder:Enter your email}]
					[f:group#bd
						[f:label!#bd Birdthday:]
						[f:s#day
							{for:n:1:31}[f:o{:n}]{next}
						]
						[f:s#month
							[f:o@1 January]
							[f:o@2 February]
							[f:o@3 March]
							[f:o@4 April]
							[f:o@5 May]
							[f:o@6 June]
							[f:o@7 July]
							[f:o@8 August]
							[f:o@9 September]
							[f:o@10 Octuber]
							[f:o@11 November]
							[f:o@12 December]
						]
						[f:s#year
							{for:n:2005:-1:1905}[f:o{:n}]{next}
						]
					]
				]
				[f:set{@title:Interests}{@style:horizontal}
					[f:group#int{@title:"Interests:"}{@style:radios}
						[f:r#int.1 education{@label:Education}]
						[f:r#int.2 job{@label:Job and Career}]
						[f:r#int.3 leisure{@label:Leisure time}]
						[f:r#int.4 travel{@label:Travel}]
					]
					[f:group#com{@title:"Commitment:"}{@style:radios}
						[f:r#com.2{@label:Mild}]
						[f:r#com.3{@label:Fair}{@bool:checked}]
						[f:r#com.4{@label:Dedicated}]
						[f:r#com.5{@label:Full}]
					]
					[f:group#el{@title:"Education level:"}{@style:radios}
						[f:r#some@el{@label:Some Shool}]
						[f:r#hs@el{@label:Highschool}]
						[f:r#college@el{@label:College}]
						[f:r#graduate@el{@label:Graduate School}]
					]
					[f:group#iss{@title:"Issues:"}{@style:radios}
						[f:c#nofly{@label:Can't fly}]
						[f:c#iss.2{@label:Vegan}]
						[f:c#issue3 married{@label:Married}]
						[f:c#issue4{@label:Unrelated}{@bool:grayed}]
					]
				]
				[f:set{@title:Message}
						[f:t#text Some initial text]
				]
				[f:submit Publish]
				[f:submit!emailing Apply [b privately]]
				[f:reset]
			]
		]
	]
	[b:aside#asides{@title:o:Enlaces}
		[nav:#search{@title:o:Búsqueda}
			[f:#s-box!search
				[f:text#s{@placeholder:Your search}]
				[f:submit.search#s-go]
			]
		]
		[nav:horizontal#menu{@title:o:Menú}
			[nav:i!! Inicio]
			[nav:i!info Información]
			[nav:i!catalogo Catálogo]
		]
		[nav:horizontal#links{@title:o:Enlaces externos}
			[nav:i!//chlewey.net Chlewey[sc _2enet]]
			[nav:i!//chlewey.org Chlewey[sc _2eorg]]
			[nav:i!//invermeq.com Invermeq]
		]
		[b:#toc {auto:toc}]
	]
 

	[b:foot#footer
		[p:section#copy {ui:copyleft 2015}, Chlewey & Interlecto ({ui:twitter [!twitter:interlecto @interlecto]})]
		[p:section This sites complains with {ui:html5 HTML} and {ui:css3 CSS}.]
	]
	[script:js!//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js]
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

echo "<!--\nalias ";
var_dump(ILM::$nsalias);
echo "\ndic ";
print_r(ILM::$nsdic);
echo "-->\n";
?>

</html>
