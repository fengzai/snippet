<?php
/*
 * 此文件是针对新浪微博api 2.0的实现
 * OAuth2.0 参考 http://oauth.net/2/ 
 * 		 http://tools.ietf.org/html/draft-ietf-oauth-v2-31
 * 新浪微博OAuth api参考 http://open.weibo.com/wiki/Oauth2
 * @author taoxu
 * @link http://open.weibo.com/wiki/
 * @example
	$ot = new SinaWeiboApi();
	$ot->setAppKey("YOUR_KEY");
	$ot->setAppSecret("YOUR_SECRET");
	$ot->setRedirectUri("http://YOUR_CALLBACK_URL");
	//授权Url
	echo $ot->getLoginUrl();
	//回调
	$at = json_decode($ot->getAccessToken($_GET['code']));
	//每次调用api时需判断token是否过期$at->expires_in 为token声明周期, 单位s
	$us = json_decode($ot->usersShow($at->access_token, $at->uid));
 */
class SinaWeiboApi{
	/**
	 * 新浪微博APP KEY
	 */
	protected $appKey;
	
	/**
	 * 新浪微博APP SECRET
	 */
	protected $appSecret;

	/**
	 * 新浪微博回调URI
	 * 应与新浪平台所配置的URI一致 UPDATE: 新浪取消了平台设置
	 */
	protected $redirectUri;

	/**
	 * 设置app key
	 */	
	public function setAppKey($appKey){
		$this->appKey = $appKey;
	}

	/**
	 * 设置app secret
	 */
	public function setAppSecret($appSecret){
		$this->appSecret = $appSecret;
	}

	/**
	 * 设置返回uri, 需和新浪平台设置uri一致 UPDATE: 新浪取消了平台设置
	 */
	public function setRedirectUri($redirectUri){
		$this->redirectUri = urlencode($redirectUri);
	}

	/**
	 *新浪微博获取login url
	 * 用于让用户去授权
	 * @param 可选 一个返回参数, 用户跳转回来时会携带此参数 state=$custom
	 * @return @url
	 */
	public function getLoginUrl($custom=null){
		if(empty($this->appKey)){
			return false;
		}
		if(empty($this->redirectUri)){
			return false;
		}

		$url="https://api.weibo.com/oauth2/authorize";
		$url .= "?client_id=" . $this->appKey;
		$url .= "&redirect_uri=" . $this->redirectUri;
		$url .= "&response_type=code";
		if($custom){
			$url .= "&state=" . urlencode($custom);
		}
		$url .= "&display=default";
		$url .= "&forcelogin=false";
		//$url .= "&language=en";
		return $url;
	}

	/**
	 * 通过code获取access token
	 * method POST
	 * 数据编码使用application/x-www-form-urlencoded
	 * @param $code
	 * @return access token json format
	 */
	public function getAccessTokenOriginal($code){
		if(!$code){
			return false;
		}
		if(empty($this->appKey)){
			return false;
		}
		if(empty($this->appSecret)){
			return false;
		}
		if(empty($this->redirectUri)){
			return false;
		}
		$fs=fsockopen("ssl://api.weibo.com",443,$errno,$errstr,30);
		if(!$fs){
			return false;
		}
		$data = "";
		$data .= "client_id=" . $this->appKey;
		$data .= "&client_secret=" . $this->appSecret;
		$data .= "&grant_type=authorization_code";
		$data .= "&code=" . $code;
		$data .= "&redirect_uri=" . $this->redirectUri;

		$in = "POST https://api.weibo.com/oauth2/access_token HTTP/1.1";
		$in .= "\r\n";
		$in .= "Host: api.weibo.com";
		$in .= "\r\n";
		$in .= "Content-Type: application/x-www-form-urlencoded";
		$in .= "\r\n";
		$in .= "Content-Length: " . strlen($data);
		$in .= "\r\n";
		$in .= "Connection: Close";
		$in .= "\r\n";
		$in .= "\r\n";
		$in .= $data;
		$in .= "\r\n";
		$in .= "\r\n";
		fwrite($fs, $in, strlen($in));
		$isHeader = true;
		$response = "";
		for (;;) {
			if(feof($fs)){
				break;
			}
			$line = fgets($fs);
			if(!$isHeader){
				$response .= $line;
			}
			if($line === "\r\n"){
				$isHeader = false;
			}
		}
		fclose($fs);
		$response = trim($response);
		return $response;
	}
	
	/**
	 * @TODO
	 * Oauth2.0/Y Sina/N
	 * refresh token
	 * method POST
	 * 数据编码使用application/x-www-form-urlencoded 
	 * @param $code
	 * @return access token json format
	 * if your php dont have curl extension then you should use getAccessTokenOriginal
	 */
	public function refreshAccessToken($accessToken){
		if(!$accessToken){
			return false;
		}
		if(empty($this->appKey)){
			return false;
		}
		if(empty($this->appSecret)){
			return false;
		}
		
		$url = "https://api.weibo.com/oauth2/access_token";
		
		$data = "";
		$data .= "client_id=" . $this->appKey;
		$data .= "&client_secret=" . $this->appSecret;
		$data .= "&grant_type=refresh_token";
		$data .= "&refresh_token=" . $accessToken;

		$response = $this->curlPost($url, $data);
		return $response;
	}

	
	/**
	 * 通过code获取access token
	 * method POST
	 * 数据编码使用application/x-www-form-urlencoded 
	 * @param $code
	 * @return access token json format
	 * if your php dont have curl extension then you should use getAccessTokenOriginal
	 */
	public function getAccessToken($code){
		if(!$code){
			return false;
		}
		if(empty($this->appKey)){
			return false;
		}
		if(empty($this->appSecret)){
			return false;
		}
		if(empty($this->redirectUri)){
			return false;
		}
		
		$url = "https://api.weibo.com/oauth2/access_token";
		
		$data = "";
		$data .= "client_id=" . $this->appKey;
		$data .= "&client_secret=" . $this->appSecret;
		$data .= "&grant_type=authorization_code";
		$data .= "&code=" . $code;
		$data .= "&redirect_uri=" . $this->redirectUri;

		$response = $this->curlPost($url, $data);
		return $response;
	}

	/**
	 * 获取用户信息
	 * method GET
	 * @param $accessToken
	 * @param $uid 用户id
	 * @return user info json format 
	 */
	public function usersShow($accessToken, $uid){
		if(!$accessToken){
			return false;
		}
		if(!$uid){
			return false;
		}
		$usUrl = "https://api.weibo.com/2/users/show.json";
		$usUrl .= "?access_token=" . $accessToken;
		$usUrl .= "&uid=" . $uid;
		$response = file_get_contents($usUrl);
		if(!$response){
			return false;
		}
		return $response;
	}

	/**
	 * 搜索某某一话题的微博
	 * method GET
	 * @param $accessToken
	 * @param $q 话题名
	 * @param $count 每页微博数
	 * @param $page 一共几页
	 * @return topics json format 
	 */
	public function searchTopics($accessToken, $q, $count=10, $page=1){
		if(!$accessToken){
			return false;
		}
		if(!$q){
			return false;
		}

		$stUrl = "https://api.weibo.com/2/search/topics.json";
		$stUrl .= "?access_token=" . $accessToken;
		$stUrl .= "&q=". urlencode($q);
		$stUrl .= "&count=" . $count;
		$stUrl .= "&page=" . $page;
		$response = file_get_contents($stUrl);
		if(!$response){
			return false;
		}
		return $response;
	}
	
	/**
	 *获取最新的提到登录用户的微博列表，即@我的微博
	 * method: GET
	 * 必选: M 可选: O
	 * @param $accessToken	M	请求token
	 * @param $sinceId	O	返回ID比之大的微博
	 * @param $maxId	O	返回ID比之小的微博
	 * @param $count	O	单条返回记录数 default 50
	 * @param $page		O	返回结果的页码 default 1
	 * @param $filterByAuthor	O	作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0
	 * @param $filterBySource	O	来源筛选类型，0：全部、1：来自微博、2：来自微群，默认为0
	 * @param $filterByType		O	原创筛选类型，0：全部微博、1：原创的微博，默认为0
	 * @param $trimUser	O	返回值中user字段开关，0：返回完整user字段、1：user字段仅返回user_id，默认为0
	 * @return data json format
	 */
	public function statusesMentions($accessToken, $sinceId=0, $maxId=0, $count=50, $page=1, $filterByAuthor=0, $filterBySource=0, $filterByType=0, $trimUser=0){
		if(!$accessToken){
			return false;
		}
		$smUrl = "https://api.weibo.com/2/statuses/mentions.json";
		$smUrl .= "?access_token=" . $accessToken;
		$smUrl .= "&since_id=" . $sinceId;
		$smUrl .= "&max_id=" . $maxId;
		$smUrl .= "&count=" . $count;
		$smUrl .= "&page=" . $page;
		$smUrl .= "&filter_by_author=" . $filterByAuthor;
		$smUrl .= "&filter_by_source=" . $filterBySource;
		$smUrl .= "&filter_by_type=" . $filterByType;
		$smUrl .= "&trim_user=" . $trimUser;
		$response = file_get_contents($smUrl);
		if(!$response){
			return false;
		}
		return $response;
	}
	
	/**
	 *获取最新的提到登录用户的微博ids，即@我的微博
	 * method: GET
	 * 必选: M 可选: O
	 * @param $accessToken	M	请求token
	 * @param $sinceId	O	返回ID比之大的微博
	 * @param $maxId	O	返回ID比之小的微博
	 * @param $count	O	单条返回记录数 default 50
	 * @param $page		O	返回结果的页码 default 1
	 * @param $filterByAuthor	O	作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0
	 * @param $filterBySource	O	来源筛选类型，0：全部、1：来自微博、2：来自微群，默认为0
	 * @param $filterByType		O	原创筛选类型，0：全部微博、1：原创的微博，默认为0
	 * @return data json format
	 */
	public function statusesMentionsIds($accessToken, $sinceId=0, $maxId=0, $count=50, $page=1, $filterByAuthor=0, $filterBySource=0, $filterByType=0){
		if(!$accessToken){
			return false;
		}
		$smUrl = "https://api.weibo.com/2/statuses/mentions/ids.json";
		$smUrl .= "?access_token=" . $accessToken;
		$smUrl .= "&since_id=" . $sinceId;
		$smUrl .= "&max_id=" . $maxId;
		$smUrl .= "&count=" . $count;
		$smUrl .= "&page=" . $page;
		$smUrl .= "&filter_by_author=" . $filterByAuthor;
		$smUrl .= "&filter_by_source=" . $filterBySource;
		$smUrl .= "&filter_by_type=" . $filterByType;
		$response = file_get_contents($smUrl);
		if(!$response){
			return false;
		}
		return $response;
	}

	/**
	 * 根据微博id获取其内容
	 * method: GET
	 * @param $accessToken	token
	 * @param $id	微博id
	 * @return data json format
	 */
	public function statusesShow($accessToken, $id){
		if(empty($accessToken) || empty($id)){
			return false;
		}
		$ssUrl = "https://api.weibo.com/2/statuses/show.json";
		$ssUrl .= "?access_token=" . $accessToken;
		$ssUrl .= "&id=" . $id;		
		$response = file_get_contents($ssUrl);
		if(!$response){
			return false;
		}
		return $response;
	}
	
	/**
	 * 根据微博id获取转发评论数目
	 * method: GET
	 * @param $accessToken	token
	 * @param $ids	微博id  like 9/9,10 多个用,号分开, 最多100
	 * @return data json format
	 */
	public function statusesCount($accessToken, $ids){
		if(empty($accessToken) || empty($id)){
			return false;
		}
		$scUrl = "https://api.weibo.com/2/statuses/count.json";
		$scUrl .= "?access_token=" . $accessToken;
		$scUrl .= "&ids=" . $ids;		
		$response = file_get_contents($scUrl);
		if(!$response){
			return false;
		}
		return $response;
	}
	/**
	 * 发布一条微博
	 * method: POST
	 * 数据编码使用application/x-www-form-urlencoded
	 * 必选: M 可选: O
	 * @param $accessToken	M	请求token
	 * @param $status	M	微博文本内容
	 * @param $lat float	O	纬度，有效范围：-90.0到+90.0，+表示北纬，默认为0.0
	 * @param $long float	O	经度，有效范围：-180.0到+180.0，+表示东经，默认为0.0
	 * @param $annotations	O	元数据 自定义 json format 长度<512
	 * @return data json format
	 */
	public function statusesUpdate($accessToken, $status, $lat=0.0, $long=0.0, $annotations=null){
		if(empty($accessToken) || empty($status)){
			return false;
		}
		$url = "https://api.weibo.com/2/statuses/update.json";
		
		$data = "";
		$data .= "access_token=" . $accessToken;
		$data .= "&status=" . urlencode($status);
		$data .= "&lat=" . $lat;
		$data .= "&long=" . $long;
		if($annotations !== null){
			$suUrl .= "&annotations=" . urlencode($annotations);
		}
		$response = $this->curlPost($url, $data);
		return $response;
	}
	
	/**
	 * 上传图片并发布微博
	 * method: POST
	 * 数据编码 multipart/form-data
	 * 必选: M 可选: O
	 * @param $accessToken	M	请求token
	 * @param $status	M	微博文本内容
	 * @param $pic		M	!完整路径 要上传的图片，仅支持JPEG、GIF、PNG格式，图片大小小于5M
	 * @param $lat		O	纬度，有效范围：-90.0到+90.0，+表示北纬，默认为0.0
	 * @param $long		O	经度，有效范围：-180.0到+180.0，+表示东经，默认为0.0
	 * @param $annotations	O	元数据 自定义 json format 长度<512
	 * @return data json format
	 */
	public function statusesUpload($accessToken, $status, $pic, $lat=0.0, $long=0.0, $annotations=null){
		if(empty($accessToken) || empty($status) || empty($pic)){
			return false;
		}
		$url = "https://upload.api.weibo.com/2/statuses/upload.json";
		$data = array(
			"access_token" => $accessToken,
			"status" => urlencode($status),
			"pic" => "@" . $pic,
			"lat" => $lat,
			"long" => $long,
		);
		if($annotations !== null){
			$data["annotations"] = urlencode($anotations);
		}
		$response = $this->curlPost($url, $data);
		return $response;
	}
	
	/**
	 * 查看请求限制
	 * method: GET
	 * 必选: M 可选: O
	 * @param $accessToken	M	请求token
	 * @return data json format
	 */
	public function accountRateLimitStatus($accessToken){
		if(empty($accessToken)){
			return false;
		}
		$arlsurl = "https://api.weibo.com/2/account/rate_limit_status.json";
		$arlsUrl .= "?access_token=" . $accessToken;
		$response = file_get_contents($arlsUrl);
		if(!$response){
			return false;
		}
		return $response;
	}

	/**
	 * create context for file_get_contents
	 * @param $method GET/POST
	 * @param $header like array("Host"=>"www.google.com")
	 * @return $context
	 */
	protected function createContext($method, $header){
		$headerString = "";
		foreach ($header as $k=>$v){
			$headerString .= $k . ": " . $v . "\r\n";	
		}
		$options = array(
			"http" => array(
				"method" => $method,
				"header" => $headerString,
			)
		);
		$context = stream_context_create($options);
		return $context;
	}

	/**
	 *Curl HTTP GET
	 */
	protected function curlGet($url){
		if(empty($url)){
			return false;	
		}
		$ci = curl_init();
		$coa = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			//CURLOPT_HTTPHEADER => array(),
		);
		curl_setopt_array($ci, $coa);
		$response = curl_exec($ci);
		curl_close($ci);
		return $response;
	}

	/**
	 *Curl HTTP POST 
	 */
	protected function curlPost($url, $data){
		if(empty($url) || empty($data)){
			return false;	
		}
		$ci = curl_init();
		$coa = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $data,
			//CURLOPT_HTTPHEADER => array(),
		);
		curl_setopt_array($ci, $coa);
		$response = curl_exec($ci);
		curl_close($ci);
		return $response;
	}

	/**
	 * unchunk HTTP/1.1 response data
	 * @TODO
	 */
	protected function unchunk($data) {
		$fp = 0;
		$outData = "";
		while ($fp < strlen($data)) {
			$rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
			$num = hexdec(trim($rawnum));
			$fp += strlen($rawnum);
			$chunk = substr($data, $fp, $num);
			$outData .= $chunk;
			$fp += strlen($chunk);
		}
		return $outData;
	}

}

