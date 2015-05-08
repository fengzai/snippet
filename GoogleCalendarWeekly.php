<?php
/*=============================================================================
#     FileName: GoogleCalendarChecker.php
#         Desc: 自动发周报
	 	每天执行昨天的event, 如果是周五执行昨天的和当天的
	 	所以我只需要周 2,3,4,5执行
#       Author: Cloud Tao
#        Email: txthinking@gmail.com
#     HomePage: http://blog.txthinking.com
#      Version: 0.0.1
#   LastChange: 2012-10-20 15:02:48
#      History:
=============================================================================*/
$username = "txthinking@gmail.com";
$password = "";
$sound = '/home/tx/Music/gmail-sound.wav';
$storeFile = "/home/tx/workspace/temp/Data/GoogleCalendarChecker";
if(!file_exists($storeFile)){
	touch($storeFile);
}
require_once '/home/tx/workspace/tx/php/classes/TX_Http_Curl.php';
require_once '/home/tx/workspace/tx/php/classes/TX_String.php';
$txsr = TX_Http_Curl::getInstance();
$txsr->setUrl('https://www.google.com/calendar/feeds/amaenc22lrakak72hvrj00rhms%40group.calendar.google.com/private-14909f4996a58cfafe08f8f659137e0e/basic?hl=en');
$txsr->setUserPwd($username, $password);
$result = $txsr->send();
//echo $result;exit; //取得最近25条数据

#处理
$txs = new TX_String();
#分离entry
$txs->setString($result);
$regular = "/\<entry\>[\S\s]*?\<\/entry\>/";
$entrys = $txs->getByRegular($regular);

#执行昨天的
$time = strtotime("-1 day");
$timeRegular = sprintf("%s\040%s\040%d,\040%d", date("D", $time), date("M", $time), date("j", $time), date("Y", $time));
$regular = "/<entry>[\S\s]*?<title\040type='html'>([\S\s]*?)<\/title>[\S\s]*?<content\040type='html'>When:\040({$timeRegular})[\S\s]*?(Event\040Description:\040([\S\s]*?))?<\/content>[\S\s]*?<\/entry>/";
foreach ($entrys[0] as $v){
	$txs->setString($v);
	$event = $txs->getByRegular($regular);
	//var_dump($event[1], $event[2], $event[3], $event[4]);exit;
	// 2 日期, 1 title, 4 描述
	for($i=0; $i<count($event[1]); $i++){
		$line = sprintf("[%s]<br/>\n%s<br/><br/>\n\n", $event[2][$i], $event[1][$i]);
		file_put_contents($storeFile, $line, FILE_APPEND | LOCK_EX);
	}
}

#如果是周五也执行今天的, 并发邮件, 提醒
if (date('w') == 5){
	//执行今天的
	$time = time();
	$timeRegular = sprintf("%s\040%s\040%d,\040%d", date("D", $time), date("M", $time), date("j", $time), date("Y", $time));
	$regular = "/<entry>[\S\s]*?<title\040type='html'>([\S\s]*?)<\/title>[\S\s]*?<content\040type='html'>When:\040({$timeRegular})[\S\s]*?(Event\040Description:\040([\S\s]*?))?<\/content>[\S\s]*?<\/entry>/";
	foreach ($entrys[0] as $v){
		$txs->setString($v);
		$event = $txs->getByRegular($regular);
		//var_dump($event[1], $event[2], $event[3], $event[4]);exit;
		// 2 日期, 1 title, 4 描述
		for($i=0; $i<count($event[1]); $i++){
            $line = sprintf("[%s]<br/>\n%s<br/><br/>\n\n", $event[2][$i], $event[1][$i]);
			file_put_contents($storeFile, $line, FILE_APPEND | LOCK_EX);
		}
	}

	//发邮件
	require("/home/tx/workspace/tx/php/classes/TX_Mail.php");
	$mail = new TX_Mail();
	$mail->setServer('', "25");
	$mail->setAuth('', '');
	$mail->setFrom('', '');
	$mail->setTo('', '');
	$mail->setSubject(sprintf("周报[%s-%s]陶旭", date("Y-m-d", strtotime("-4 days")), date("Y-m-d")));
	$bodyFooter = <<<HTML
HTML;
	$mail->setBody(file_get_contents($storeFile) . $bodyFooter);
	//$mail->setAttachment('aa.png', '/home/tx/Pictures/aa.png');
	$mail->setHtml(true); //this method no effect
	$r = $mail->send();

	//通知主子
	if($r){
		$outputTitle = "旭哥好! 周报已发送!";
		$outputContent = "下班啦! 要不赶不上二路汽车了!";
	}
	if(!$r){
		$outputTitle = "失败! 周报发送失败!";
		$outputContent = "我草! 失败了!";
	}
	$command = "env DISPLAY=:0.0 notify-send \"${outputTitle}\" \"${outputContent}\" && aplay ${sound} 2>/dev/null";
	exec($command);

	//重命名文件
	rename($storeFile, $storeFile . date("-Y-m-d"));
}

