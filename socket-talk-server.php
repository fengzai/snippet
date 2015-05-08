#!/home/tx/ln/php -q
<?php
/**
* socket talk server
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
* @version 0.1 beta
*/
error_reporting(E_ALL);
set_time_limit(0);

ob_implicit_flush();

$address = '192.168.1.9';
$port = 30001;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($socket, $address, $port);

socket_listen($socket, 5);

while (true){
	$msgSocket = socket_accept($socket);

	$msg = "\nWelcome!\n"
			. "This is a talk program!\n"
			. "Type 'quit' for quit!\n"
			. "OK! Let's talk!\n";
	socket_write($msgSocket, $msg, strlen($msg));
	
	while (true){
		$clientSay = @socket_read($msgSocket, 2048, PHP_NORMAL_READ);
		if ($clientSay === false){
			echo "Notice: Client closed!\n";
			break;
		}
        $clientSay = iconv('gbk', 'utf-8', trim($clientSay));
		if (!$clientSay) {
			continue;
		}
		if ($clientSay == 'quit') {
			break;
		}
		if ($clientSay == 'shutdown') {
			socket_close($msgsock);
			break 2;
		}
		echo "Client:" . $clientSay . "\n";
		
		$notice = "Notice: Please wait for a response...\n";
		socket_write($msgSocket, $notice, strlen($notice));
		
		$serverSay = "Server:" . iconv('utf-8', 'gbk', trim(fgets(STDIN))) . "\n";
		socket_write($msgSocket, $serverSay, strlen($serverSay));
	}
	socket_close($msgSocket);
}

socket_close($socket);