#!/home/tx/ln/php -q
<?php
/**
 * dynamic dns use oray
 * Explain: http://goo.gl/KK1Qm
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 */
date_default_timezone_set('Asia/Hong_Kong');

$username = "txthinking"; //username
$password = $argv[1]; //password
$hostname = "txthinking.vicp.cc"; //domain which you want to use

/**
 * 获取ip
 */
/*
 * this is use ip138.com data
 */
 /*$result = file_get_contents('http://www.ip138.com/ip2city.asp');
$count = substr_count($result, '您的IP地址是');
if (!$count){
	$result = iconv('gbk', 'utf-8', $result);
}
preg_match_all('/\[(.*?)\]/', $result, $resultsArray);
$myip = $resultsArray[1][0]; */
/*
* this is use whatismyip.com data
*/
$myip = file_get_contents('http://automation.whatismyip.com/n09230945.asp');
/**
 * 开始解析dns
 */
$url = 'http://ddns.oray.com/ph/update?hostname='
	. $hostname
	. '&myip='
	. $myip;

$request = curl_init();
$xArray = array(
	CURLOPT_URL => $url,
	CURLOPT_HEADER => false,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_USERAGENT => 'TX',
	CURLOPT_USERPWD => $username . ':' . $password
);
curl_setopt_array($request, $xArray);
$data = curl_exec($request);
curl_close($request);
echo $data . "\n";