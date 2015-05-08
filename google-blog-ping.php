#!/home/tx/ln/php -q
<?php
/**
 * google blog search ping
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 0.9
 */
$siteName = 'TX\'s Blog';
$siteUrl = 'http://blog.txthinking.com';
$siteUpdateUrl = $argv[1]; //new article url
$siteXml = 'http://blog.txthinking.com/feeds/posts/default';
$data = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<methodCall>
  <methodName>weblogUpdates.extendedPing</methodName>
  <params>
    <param>
      <value>$siteName</value>
    </param>
    <param>
      <value>$siteUrl</value>
    </param>
    <param>
      <value>$siteUpdateUrl</value>
    </param>
    <param>
      <value>$siteXml</value>
    </param>
  </params>
</methodCall>
EOF;

$url = 'http://blogsearch.google.com/ping/RPC2';
$request = curl_init();
$xArray = array(
	CURLOPT_URL => $url,
	CURLOPT_HEADER => false,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $data,
	CURLOPT_USERAGENT => 'TX'
);
curl_setopt_array($request, $xArray);
$result = curl_exec($request);
curl_close($request);
if (strpos($result, "<boolean>0</boolean>")){
	echo "Ping Success\n";
}else {
	echo "Ping Failure\n";
}