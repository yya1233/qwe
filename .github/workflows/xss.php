<?php
/**
 * index.php ���ܵ���ҳ
 * ----------------------------------------------------------------
 * OldCMS,site:http://www.oldcms.com
 */
include('init.php');
$do=Val('do','REQUEST');
$dos=array('code','api','do');
if(!in_array($do,$dos)) $do='302';
include(ROOT_PATH.'/source/'.$do.'.php');
?>