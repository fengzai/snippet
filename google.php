<?php
/**
 * @file google.php
 * @brief google OAuth login
 * @author cloud@txthinking.com
 * @version 0.0.1
 * @date 2013-10-22
 */
/**
example:

    // just a fucking code, can be saved in cache DB
    $state = md5(rand());

    // login url
    $url = GG_GetURL($state);
    // callback
    $user = GG_AllInOne($state);

$url:

    a string

$user:

    Array
    (
        [user_id] => 109347882288827801895
        [name] => cloud@txthinking.com
        [email] => cloud@txthinking.com
    )

 */

/**
 * MUST define follow const
 */
defined("GOOGLE_CLIENT_ID")     || die("unset GOOGLE_CLIENT_ID");
defined("GOOGLE_CLIENT_SECRET") || die("unset GOOGLE_CLIENT_SECRET");
defined("GOOGLE_REDIRECT_URI")  || die("unset GOOGLE_REDIRECT_URI");

require_once("xhttp.php");

/**
 * @brief GG_AllInOne
 *
 * @param $state
 *
 * @return
 */
function GG_AllInOne($state){
    $r = GG_CheckCSRF($state);
    if(!$r){
        return false;
    }
    if(!array_key_exists("code", $_GET)){
        return false;
    }
    $token = GG_GetToken($_GET['code']);
    $idToken = GG_GetIDToken($token);
    $r = GG_VerifyIDToken($idToken);
    return $r;
}

/**
 * @brief GG_CheckCSRF
 * 检查CSRF
 *
 * @param $state
 *
 * @return
 */
function GG_CheckCSRF($state){
    if(!array_key_exists("state", $_GET)){
        return false;
    }
    if ($state != $_GET['state']){
        return false;
    }
    return true;
}

/**
 * @brief GG_GetURL
 * 获取认证url
 * state is used for fucking CSRF
 *
 * @param $state
 *
 * @return
 */
function GG_GetURL($state){
    $url = "https://accounts.google.com/o/oauth2/auth";
    $p = $url . "?";
    $params = array(
        "client_id"     => GOOGLE_CLIENT_ID,
        "response_type" => "code",
        "scope"         => "openid email",
        "redirect_uri"  => GOOGLE_REDIRECT_URI,
        "state"         => $state,
    );
    foreach($params as $k=>$v){
        $v = urlencode($v);
        $p .= sprintf("%s=%s&", $k, $v);
    }
    return $p;
}

/**
 * @brief GG_GetToken
 * 获取token
 *
 * @param $code
 *
 * @return
 */
function GG_GetToken($code){
    $url = "https://accounts.google.com/o/oauth2/token";
    $fields = array(
        "code"          => $code,
        "client_id"     => GOOGLE_CLIENT_ID,
        "client_secret" => GOOGLE_CLIENT_SECRET,
        "redirect_uri"  => GOOGLE_REDIRECT_URI,
        "grant_type"    => "authorization_code",
    );
    $data = X_POST($url, $fields);
    return $data;
}

/**
 * @brief GG_GetIDToken
 * id token
 *
 * @param $token
 *
 * @return
 */
function GG_GetIDToken($token){
    $r = json_decode($token, true);
    if($r === false ||
        !array_key_exists("id_token", $r)){
            return false;
        }
    return $r['id_token'];
}

/**
 * @brief GG_VerifyIDToken
 * 防止第三方截包
 *
 * @param $idToken
 *
 * @return if ok return user_id and email
 */
function GG_VerifyIDToken($idToken){
    $url = "https://www.googleapis.com/oauth2/v1/tokeninfo";
    $params = array(
        "id_token" => $idToken,
    );
    $data = X_GET($url, $params);
    $r = json_decode($data, true);
    $data = array();
    $data['user_id'] = $r['user_id'];
    $data['name'] = $r['email'];
    $data['email'] = $r['email'];
    return $data;
}

/**
 * @brief GG_GetEmail
 * 获取email
 * JWT: http://openid.net/specs/draft-jones-json-web-token-07.html
 *
 * @param $idToken
 *
 * @return
 */
function GG_GetEmail($idToken){
    $r = explode(".", $idToken);
    $r = json_decode(base64_decode($r[1]), true);
    return $r['email'];
}



