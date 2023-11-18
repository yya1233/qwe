<?php

include('init.php');
include(ROOT_PATH.'/source/vendor/autoload.php');
$do=Val('callback','REQUEST'); 
$dos=array('index','login','project','module','code','jsonp','startcron','do','register','user','keepsession','keepsessionssss');
if(!in_array($do,$dos)) $do='index';
include(ROOT_PATH.'/source/'.$do.'.php');

?>