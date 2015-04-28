<?php
require_once 'tag.php';

class ILMnav extends ILMtag {
};

class ILMhnav extends ILMsection {
};

class ILMnavli extends ILMli {
}

ILM::add_namespace('nav','ILMnav');
ILM::add_class('li','ILMnavli','nav','i');
ILM::add_class('div.hnav','ILMnavli','nav','horizontal');
ILM::add_class('nav.navbar','ILMnav','nav','bar');


?>
