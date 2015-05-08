#!/home/tx/ln/php
<?php
/**
 * 瞎胡闹
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 */
date_default_timezone_set('Asia/Hong_Kong');

$queryString = empty($argv[1]) ? '美女' : $argv[1]; //要查询字符串, 如果加参数便取参数否则取默认美女值
$start = 0; //以20叠加
$path = '/home/tx/Pictures/'; //文件保存路径

#建立文件夹
exec('mkdir ' . $path . $queryString);

#main function
function goSpider($queryString, $start, $path){
	$queryUrlString = rawurlencode($queryString);
	$results = file_get_contents('https://www.google.com/search?q='
	. $queryUrlString
	. '&hl=en&newwindow=1&biw=1440&bih=786&gbv=2&tbm=isch&ei=DUjsTpXiCeWViQfxsNCtBw&start='
	. $start
	. '&sa=N');
	preg_match_all('/\/imgres\?imgurl=(.*?)&amp;imgrefurl=/', $results, $resultsArray);
	#$resultsArray[0]是匹配的整个正则表达式的内容 $resultsArray[1]是匹配的第一个小括号的内容
	foreach ($resultsArray[1] as $v){
		$temp = file_get_contents($v);
		#如果未取到则进行下次循环
		if ($temp == false){
			continue;
		}
		#文件后缀名
		$suffix =  substr($v, strrpos($v, '.'));

		#文件最终名字
		$fileName = $path . $queryString. '/' . time() . $suffix;

		#写入文件
		file_put_contents($fileName, $temp);
	}

	#改变$start 递归再次爬行
	$start += 20;
	if ($start == 100){
		exit("旭哥, 我爬完了!\n");
	}
	goSpider($queryString, $start, $path);
}

#spider开始爬
goSpider($queryString, $start, $path);
