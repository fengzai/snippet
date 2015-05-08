#!/home/tx/ln/php -q
<?php
/**
 * check gmail inbox
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 0.9
 */
$username = $argv[1];
$password = $argv[2];
$sound = '/home/tx/Music/gmail-sound.wav';
require_once '/home/tx/workspace/tx/php/classes/TX_Http_Curl.php';
require_once '/home/tx/workspace/tx/php/classes/TX_String.php';
$txsr = TX_Http_Curl::getInstance();
$txsr->setUrl('https://mail.google.com/mail/feed/atom/');
$txsr->setUserPwd($username, $password);
$result = $txsr->send();
//处理
$txs = new TX_String();
$txs->setString($result);
$mailInfo = $txs->getByRegular('/<entry>[\S\s]*?<title>(.*)<\/title>[\S\s]*?<name>(.*)<\/name>[\S\s]*?<email>(.*)<\/email>[\S\s]*?<\/entry>/');
//$mailInfo[0] 所有邮件数组 $mailInfo[1] 所有标题数组 $mailInfo[2] 所有发件人姓名 $mailInfo[3] 所有发件人email

$mailCount = count($mailInfo[0]); //未读邮件数
if ($mailCount > 0){
	$outputTitle = "旭哥, 有 $mailCount 封新邮件!";
	$outputcontent = '';
	for ($i=0; $i<$mailCount; $i++){
		$outputcontent .= $mailInfo[1][$i] . "  ::  " . $mailInfo[2][$i] . "\n";
	}
	$command = "env DISPLAY=:0.0 notify-send \"${outputTitle}\" \"${outputcontent}\" && aplay ${sound} 2>/dev/null";
	//如果需要声音提示在$command后面加上 && aplay /home/tx/Music/大地.mp3 2>/dev/null
	exec($command);
}
