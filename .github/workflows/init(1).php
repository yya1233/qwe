<?php
/**
 * init.php 初始化信息
 * ----------------------------------------------------------------
 * OldCMS,site:http://www.oldcms.com
 */
define('IN_OLDCMS',true);
define('ROOT_PATH',dirname(__FILE__));

include(ROOT_PATH.'/config.php');
//调试模式
if($config['debug']==false) error_reporting(0);

define('URL_ROOT',$config['urlroot']);
define('URL_PROJECT',$config['urlproject']);
define('URL_REWRITE',$config['urlrewrite']);
define('MYSQL_DATABASE_NAME',$config['database']);
define('REGISTER',$config['register']);
define('MAIL_AUTH',$config['mailauth']);
define('FILE_PATH',$config['filepath']);
define('FILE_PREFIX',$config['fileprefix']);
define('TEMPLATE_PATH',ROOT_PATH.'/themes/'.$config['template']);
define('EXPIRES',$config['expires']);
define('TABLE_PREFIX',$config['tbPrefix']);
define("XFF_ENABLE", false);	//是否使用HTTP_X_FORWARDED_FOR的地址来代替REMOTE_ADDR，当且仅当存在反代的情况下才须开启，开启须谨慎！

include(ROOT_PATH.'/source/function.php');
include(ROOT_PATH.'/source/global.func.php');
include(ROOT_PATH.'/source/class/User.class.php');

//显示设置
$show=$config['show'];

//积分设置
$pointConfig=$config['point'];

//mail设置
$mailConfig=$config['mail'];

//mail API
$mailapi = $config['waimail'];
$mailapiurl = $config['waiurl'];

//时区设置
@date_default_timezone_set($config['timezone']);

//url设置
$url=array();
$url['root']			=$config['urlroot'];
$url['imagePath']		=FILE_PREFIX.'/image/';
$url['avatarPath']		=FILE_PREFIX.'/avatar/';
$url['fieldPath']		=FILE_PREFIX.'/field/';
$url['themePath']		=$url['root'].'/themes/'.$config['theme'];

$urlDoArray=array('login','register');
if($config['urlrewrite']){
	$url['rewrite']		=1;
	foreach($urlDoArray as $value){
		$url[$value]=$url['root']."/{$value}/";
	}
}else{
	$url['rewrite']		=0;
	foreach($urlDoArray as $value){
		$url[$value]=$url['root'].'/bdstatic.com/?callback='.$value;
	}
}

//用户初始化
$user=new User();
if($user->userId>0){
	$show['user']=array(
		'userId' 		=>$user->userId,
		'userName'		=>$user->userName,
		'adminLevel'	=>$user->adminLevel,
		'token'			=>$user->token,
		'huiyuan'		=>$user->huiyuan,
		'huiyuantime'	=>$user->huiyuantime
		//'signature'		=>$user->signature
	);
}

unset($config); //清理config
?>