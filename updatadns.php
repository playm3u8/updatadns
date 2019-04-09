<?php
/**
 * 新网DNS自动解析官网IP
 */
ini_set("html_errors","On");
ini_set("display_errors","On");
require_once "whttp/autoload.php";

use PL\Whttp;

// 主机记录值
$name   = "app";
// 顶级域名信息
$domain = "xxx.com";
// 新网登录Cookie（最好10分钟获取执行一次避免JSESSIONID失败）
$cookie = "dcp_id=; JSESSIONID=";

$ip = getIP();

if (empty($ip)) 
{
	exit("Public IP Acquisition Failure");
}

$dnsList = getDNSList($cookie, $domain);

if (!exist($dnsList, $name, $domain)) {
	// 到这里如果没有找到就就添加
	$Result = addDoma($cookie, $ip, $name, $domain);
	exit($Result);
} else {
	// 存在就判断更新
	foreach ($dnsList["mydnsList"] as $key => $value) {
		if ($value["name"] == $name.".".$domain && $value["content"] != $ip && $value["type"] == "A") {
			// 更新IP地址
			$Result = updata($cookie, $ip, $value["content"], $value["domainId"], $value["id"], $name, $domain);
			exit($Result);
		}
	}	
}

// 获取DNS列表
$dnsList = getDNSList($cookie, $domain);
p($dnsList,true);

// 判断A记录是否存在
function exist($dnsList, $name, $domain)
{
	foreach ($dnsList["mydnsList"] as $key => $value)
	{
		if ($value["name"] == $name.".".$domain && $value["type"] == "A") {
			return true;	
		}
	}
	return false;
}

// 获取拨号IP
function getIP(){
	$ip = Whttp::get("http://2019.ip138.com/ic.asp")->core("[","]")->timeout(5)->ctimeout(5)->getBody();
	return $ip;
}

// 获取所有DNS列表
function getDNSList($cookie, $domain)
{
	$data = [
	    "skip" => 1,
	    "limit" => 10,
	    "queryDomainName" => $domain,
	    "dnsOprationSuccess" => null,
	    "isLogin" => true,
	    "queryType" => "ALL",
	    "queryRecordName" => null,
	    "record_Name" => null,
	    "content_content" => null,
	    "ttl_ttl" => 600,	
	];
	
	$rget = "http://dcp.xinnet.com/domain/dnsmanage.do?method=getDNSList";
	$http = Whttp::post($rget, merge_string($data))->timeout(5)->ctimeout(5)->cookie($cookie);
	
	$list = $http->getJson("jsonData.0");
	
	if (!$list) {
		exit("Acquisition failure");	
	} else {
		return $list;
	}
}

// 添加A记录 
function addDoma($cookie, $ip, $name, $domain)
{
	$data = [
		"domainId" => null,
		"recordType" => "A",
		"recordName" => $name,
		"content" => $ip,
		"oldContent" => null,
		"browserFingerprints" => "24eef55abb1df9d8d789a6dae7c37e64",
		"oldMx" => null,
		"isShow" => null,
		"mx" => null,
		"ttl" => 600,
		"a1" => null,
		"a2" => null,
		"a3" => null,
		"a4" => null,
		"domainName" => $domain,
		"phoneCode" => null,
		"recordId" => null,
	];
	
	$rget = "http://dcp.xinnet.com/domain/dnsmanage.do?method=addRecord";
	$http = Whttp::post($rget, merge_string($data))->timeout(5)->ctimeout(5)->cookie($cookie)->getBody();
	return $http;
}

// 更新A记录
function updata($cookie, $ip, $oldContent, $domainId, $recordId, $name, $domain)
{
	$data = [
	    "domainId" => $domainId,
	    "recordType" => "A",
	    "recordName" => $name.".".$domain,
	    "content" => $ip,
	    "oldContent" => $oldContent,
	    "browserFingerprints" => "24eef55abb1df9d8d789a6dae7c37e64",
	    "oldMx" => "--",
	    "isShow" => null,
	    "mx" => null,
	    "ttl" => 600,
	    "a1" => null,
	    "a2" => null,
	    "a3" => null,
	    "a4" => null,
	    "domainName" => $domain,
	    "phoneCode" => null,
	    "recordId" => $recordId, //580
	];
	$rget = "http://dcp.xinnet.com/domain/dnsmanage.do?method=modifyRecord";
	$http = Whttp::post($rget, merge_string($data))->timeout(5)->ctimeout(5)->cookie($cookie)->getBody();
	return $http;
}