#!/home/tx/ln/php -q
<?php
/**
 * socket client
 * 参数 &host |port
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 0.1 beta
 */
date_default_timezone_set('Asia/Hong_Kong');
//set_time_limit(0);

$host = $argv[1];
$port = empty($argv[2]) ? getservbyname('www', 'tcp') : $argv[2];
$address = gethostbyname($host);

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

$resultConnect = socket_connect($socket, $address, $port);

//send message
$in = "HEAD / HTTP/1.1\r\n";
$in .= "Host: $host\r\n";
$in .= "Connection: Close\r\n\r\n";
socket_write($socket, $in, strlen($in));

$out = '';
while ($out=socket_read($socket, 1024)){
	echo $out;
}

socket_close($socket);