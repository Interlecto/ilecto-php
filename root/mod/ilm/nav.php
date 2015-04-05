<?php
require_once 'tag.php';

class ILMnav extends ILMtag {
};

class ILMhnav extends ILMsection {
};

class ILMnavli extends ILMli {
}

ILM::add_namespace('nav','ILMhnav');
ILM::add_class('li','ILMnavli','nav','i');
ILM::add_class('div.hnav','ILMnavli','nav','horizontal');


?>
