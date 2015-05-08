<?php
/**
* @file facebook.php
* @brief facebook oauth login
* @author cloud@txthinking.com
* @version 0.0.1
* @date 2013-10-23
 */

/*
example:

    // just a fucking code, can be saved in cache DB
    $state = md5(rand());

    // get login url
    $url = FB_GetURL($state);
    // callback
    $user = FB_AllInOne($state);

$url:
    a string

$user:

    Array
    (
        [user_id] => aaaa
        [name] => Xu Tao
        [email] => tmp@ym.txthinking.com
    )
 */

defined("FACEBOOK_APP_ID")       || die("unset FACEBOOK_APP_ID");
defined("FACEBOOK_APP_SECRET")   || die("unset FACEBOOK_APP_SECRET");
defined("FACEBOOK_REDIRECT_URI") || die("unset FACEBOOK_REDIRECT_URI");

require_once("xhttp.php");

/**
 * @brief FB_GetURL 获取login URL
 *
 * @param $state
 *
 * @return
 */
function FB_GetURL($state){
    $url = "https://www.facebook.com/dialog/oauth";
    $p = $url . "?";
    $params = array(
        "client_id"     => FACEBOOK_APP_ID,
        "response_type" => "code",
        "scope"         => "email",
        "redirect_uri"  => FACEBOOK_REDIRECT_URI,
        "state"         => $state,
    );
    foreach($params as $k=>$v){
        $v = urlencode($v);
        $p .= sprintf("%s=%s&", $k, $v);
    }
    return $p;
}

/**
 * @brief FB_AllInOne
 *
 * @param $state
 *
 * @return
 */
function FB_AllInOne($state){
    $r = FB_CheckCSRF($state);
    if($r === false){
        return false;
    }
    $token = FB_GetToken();
    $userId = FB_VerifyToken($token);
    if($userId === false){
        return false;
    }
    $user = FB_GetUser($userId, $token);
    $data = array();
    $data['user_id'] = $user['id'];
    $data['name']    = $user['name'];
    $data['email']   = $user['email'];
    return $data;
}

/**
 * @brief FB_CheckCSRF
 *
 * @param $state
 *
 * @return
 */
function FB_CheckCSRF($state){
    if(!array_key_exists("state", $_GET)){
        return false;
    }
    if ($state != $_GET['state']){
        return false;
    }
    return true;
}

/**
 * @brief FB_GetToken 获取token
 *
 * @return
 */
function FB_GetToken(){
    if(!array_key_exists("code", $_GET)){
        return false;
    }
    $url = "https://graph.facebook.com/oauth/access_token";
    $params = array(
        "client_id"     => FACEBOOK_APP_ID,
        "client_secret" => FACEBOOK_APP_SECRET,
        "code"          => $_GET['code'],
        "redirect_uri"  => FACEBOOK_REDIRECT_URI,
    );
    $data = X_GET($url, $params);
    $r = explode("&", $data);
    $r = explode("=", $r[0]);
    return $r[1];
}


/**
 * @brief getAccessToken This is App Access Token
 *
 * @return
 */
function FB_GetAccessToken(){
    $url = "https://graph.facebook.com/oauth/access_token";
    $params = array(
        "client_id"     => FACEBOOK_APP_ID,
        "client_secret" => FACEBOOK_APP_SECRET,
        "grant_type"    => "client_credentials",
    );
    $data = X_GET($url, $params);
    $r = explode("=", $data);
    return $r[1];
}

/**
 * @brief FB_VerifyToken 验证token
 *
 * @param $token
 *
 * @return if ok return user_id, otherwise return false
 */
function FB_VerifyToken($token){
    $accessToken = FB_GetAccessToken();
    $url = "https://graph.facebook.com/debug_token";
    $params = array(
        "input_token"  => $token,
        "access_token" => $accessToken,
    );
    $data = X_GET($url, $params);
    $r = json_decode($data, true);
    if(!$r){
        return false;
    }
    if(!array_key_exists("data", $r)){
        return false;
    }
    if(!array_key_exists("is_valid", $r['data']) ||
        !$r['data']['is_valid']){
        return false;
    }
    if(!array_key_exists("user_id", $r['data'])){
        return false;
    }
    return $r['data']['user_id'];
}

/**
 * @brief FB_GetUser
 *
 * @param $userId
 * @param $token
 *
 * @return
 */
function FB_GetUser($userId, $token){
    $url = "https://graph.facebook.com/";
    $url = sprintf("%s%s", $url, $userId);
    $params = array(
        "access_token" => $token,
    );
    $data = X_GET($url, $params);
    $r = json_decode($data, true);
    return $r;
}

