<?php
include('init.php');
include(ROOT_PATH.'/source/vendor/autoload.php');
use apanly\BrowserDetector\Browser;
use apanly\BrowserDetector\Os;
use apanly\BrowserDetector\Device;
$browser = new Browser();
$os = new Os();
$device = new Device();
$sbw = "";
if($device->getName()!="unknown"){
	$sbw = "<br/>设备为：".$device->getName();
}
$dats = "<br/>操作系统：".$os->getName()." ".$os->getVersion()."<br/>浏览器：".$browser->getName()."(版本:".$browser->getVersion().")".$sbw;
//print_r($dats);exit;
$user_ip=get_ipip();
$userip=StripStr($user_ip);
$useripadd=urlencode(adders($user_ip).$dats);
$useragent=StripStr($_SERVER['HTTP_USER_AGENT']);
$data=Val('data','REQUEST');
$datasave = $data;
//$data = isset($_REQUEST["data"]) ? trim($_REQUEST["data"]) : ''; //如果接收不到data参数，则赋值为空。
if(empty($data)){
	indexecho("", "<h3 style=\"text-align: center;\">您的数据包可能格式有误(或者为空)或者请您重新输入。</h3><br/>");
	exit;
}
$type = "";
$formdata = "";    	//用于储存post 或者 get的所有参数
$formurl = "";   	//用于储存最终post地址
$data = explode(PHP_EOL,$data);
$formatches = parse_url(trim($data[0]));

if(stristr(trim($data[0]),"post")){
	$type = 1;
	if(!stristr(trim($formatches['path'])," HTTP/1.1")){
		$formurl = $formatches['path']." HTTP/1.1";
	}else{
		$formurl = $formatches['path'];
	}
	if(array_key_exists("query", $formatches)){
		$formdata = substr($formatches['query'],0,strlen($formatches['query'])-9);
	}else{
		$formdata = "";
	}
	$datanumber = count($data)-1;
	//$emptynumber = array_search(' ', $data);   //linux下无法搜索空值  这里涉及一个空格问题，linux下空格不为空
	foreach ($data as $key=>$value){
	    if(empty(trim($value))){
	        $emptynumber = $key;
	        break;
	    }
	}
	$psformdata = $formdata;
	if($emptynumber && $datanumber>$emptynumber){
		do {
			if(!empty(trim($data[$emptynumber]))){
				$formdata .= "&".$data[$emptynumber];
			}
			unset($data[$emptynumber]);
			$emptynumber++;
		} while ($datanumber>=$emptynumber);
	}
}elseif(stristr(trim($data[0]),"get")){
	$type = 2;
	$formurl = str_ireplace("get ","POST ", $formatches['path'])." HTTP/1.1";    	//不区分大小写替换get请求为post
	if(array_key_exists("query", $formatches)){
		$formdata = substr($formatches['query'],0,strlen($formatches['query'])-9);
	}else{
		$formdata = "";
	}
}else{
	indexecho("", "<h3 style=\"text-align: center;\">类型错误无法转换，请输入POST或者GET类型数据包</h3><br/>");
	exit;
}

//   	$formdata 为post的数据包	    $formurl为post地址	    $data为完整数据包
$contentType = "";
$dataarray = [];
$i = 0;
$randcode = "--------WebKitFormBoundary".ShortCode();
$domains = "";
foreach ($data as $key=>$value){
	if($key == 0 && $formurl){
		$dataarray[] = $formurl;
		continue;
	}
	if(stripos($value,"Content-Type") === 0){
		$contentType = $key;
		continue;
	}
	if(stripos($value,"Host:") === 0){
		$domains = explode(":",$value);
		$domains = trim($domains[1]);
	}
	if($i == 5){
		$dataarray[] = htmlspecialchars("Content-Type: multipart/form-data; boundary=".$randcode, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8', true);
	}
	$dataarray[] = $value;  //htmlspecialchars($value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8', true);
	$i++;
}
$dataarray[] = PHP_EOL;
$postdata = "";
$formdatas = [];
if(stripos($formdata, "&amp;") >= 0){
	$formdata = explode("&amp;",$formdata);
}elseif(stripos($formdata, "&") >= 0){
	$formdata = explode("&",$formdata);
}
if(!empty($formdata)){
	//parse_str($formdata,$formdatas);
	if($formdata[0]{0} == "&"){
		$formdata[0] = substr($formdata[0],1);
	}
	foreach ($formdata as $key=>$value){
		$value = explode("=", $value);
		if(!empty($value)){
			$postdata .= "--".$randcode.PHP_EOL."Content-Disposition: form-data; name=\"{$value['0']}\"".PHP_EOL.PHP_EOL.$value['1'].PHP_EOL;
		}
	}
	$postdata .= "--".$randcode."--".PHP_EOL;
}
$dataarray[] =  htmlspecialchars($postdata, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8', true);    	//htmlspecialchars($xitong, ENT_SUBSTITUTE);
indexecho($dataarray);
$db=DBConnect();
$datamd5 = md5($datasave);
$cookieExisted=$db->FirstValue("SELECT COUNT(*) FROM ".Tb('burp')." WHERE md5s='{$datamd5}' ");
$valuedatas=array(
			'burpbefore'=>urlencode($datasave),
			'burpafter'=>urlencode(implode(PHP_EOL,$dataarray)),
			'domain'=>urlencode($domains),
			'md5s'=>$datamd5,
			'ip'=>$userip,
			'ipadders'=>$useripadd,
			'useragents'=>$useragent,
			'starttime'=>time()
		);
if($cookieExisted<=0 || empty($cookieExisted)){
	$db->AutoExecute(Tb('burp'),$valuedatas);
}else{
	$db->Execute("UPDATE ".Tb('burp')." SET ip='".$userip."',ipadders='".$useripadd."',useragents='".$useragent."',starttime='".time()."' WHERE md5s='{$datamd5}' ");
}

function indexecho($data=null, $error=null){
	if(empty($data)){
		$data = "<textarea name=\"data\" style=\"margin: 0px; width: 700px; height: 400px;\" placeholder=\"请在此处放入Burp抓取的数据包内容，暂时仅支持Post、Get两种正常类型转换为Post上传类型，注意Post的参数如果是Json类型暂时不支持。返回数据会在此方框中显示。----------很多时候很多网站Get、Post类型可以互相转换使用，如果遇到这样的网站，完全可以把数据包改为上传类型，这样可以尝试SQL注入、XSS等问题，而且有的时候还可以绕过WAF的检测哦。\";></textarea> ";
	}else{
		$datas = implode(PHP_EOL,$data);
		$data = "<textarea name=\"data\" style=\"margin: 0px; width: 700px; height: 400px;\">{$datas}</textarea>";
	}
	    echo <<<EOT

<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<title>Burp Post、Get数据包转为上传multipart/form-data格式数据包工具 - 数据包格式在线转换</title>
<link rel="stylesheet" type="text/css" href="//cdn.bootcss.com/bootstrap/3.1.1/css/bootstrap.min.css?test123aa">
<style>
body,html{width:100%;height:100%;background-color:#f5f5f5}
.main{width:700px;height:400px;position:absolute;left:50%;margin-left:-350px}
.main-panel{margin-top:80px}
.text-wrap{width:600px;min-height:60px;margin:20px auto;padding:15px 0;position:relative;clear:both;font-family:	SimHei,serif;font-size:30px;text-align:center;color:#5CB85C;font-weight:600}
#footer nav{background-color:#dedede}
.main-panel {
    margin-top: 50px;
}

table {
    margin: 0;
    padding: 0;
    -webkit-tap-highlight-color: transparent;
}
.markdown-body table {
  display: block;
  width: 100%;
  overflow: auto;
  word-break: normal;
  word-break: keep-all;
}

.markdown-body table th {
  font-weight: bold;
}

.markdown-body table th,
.markdown-body table td {
  padding: 6px 10px;
  border: 1px solid #ddd;
}

.markdown-body table tr {
  background-color: #fff;
  border-top: 1px solid #ccc;
}

.markdown-body table tr:nth-child(2n) {
  background-color: #f8f8f8;
}
</style>

</head>
<body>
<div class="main">
<div class="col-md-12">
			<div class="text-wrap">
				Burp Post、Get数据包转为上传multipart/form-data格式数据包工具
			</div>
</div>
<div class="row" style="text-align: center;">
    <div class="col-lg-12">
			
			<form style="padding: 10px 15px;" id="contentForm" action="multipart.php" method="post">
				<fieldset> 
					<div id="contentShow"></div>
					<p> 
						<label for="title">burp截取数据包</label><br> 
						{$data}
					</p> 
					<p> 
					<button type="submit"  class="btn btn-success btn-lg search-btn" ><b>转换</b></button>
					</p> 
				</fieldset> 
			</form>
	</div>
</div>



EOT;
if($error){
	echo $error;
}
echo "<br/></body></html>";
}


function ShortCode($num=16){
	$str='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$len=strlen($str);
	$code='';
	for($i=0;$i<$num;$i++){
		$k=rand(0,$len-1);
		$code.=$str[$k];
	}
	return $code;
}
?>