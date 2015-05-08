<?php
/**
 * @file yahoo.php
 * @brief Yahoo Oauth 1.0
 * http://oauth.net/core/1.0/
 * @author cloud@txthinking.com
 * @version 0.0.1
 * @date 2013-10-23
 */

/**
example:

    // get login url $r['url'], and save $r['requestTokenSecret'] for callback
    $r = YH_GetUrl();
    // callback
    $user = YH_AllInOne($r['requestTokenSecret']);

$r:

    Array
    (
        [url] => a string
        [requestTokenSecret] => a string
    )

$user:

    Array
    (
        [user_id] => AMXAQQ5EH3UPMEQF2PJYWNCOD4
        [name] => Xu Tao
        [email] => fucktheworld@yahoo.com
    )

 */
defined("YAHOO_CONSUMER_KEY")    || die("unset YAHOO_CONSUMER_KEY");
defined("YAHOO_CONSUMER_SECRET") || die("unset YAHOO_CONSUMER_SECRET");
defined("YAHOO_REDIRECT_URI")    || die("unset YAHOO_REDIRECT_URI");

require_once("xhttp.php");

/**
 * @brief YH_GetUrl
 *
 * @return
 */
function YH_GetUrl(){
    $r = YH_GetRequestToken();
    if(!array_key_exists("xoauth_request_auth_url", $r) ||
        !array_key_exists("oauth_token_secret", $r)
    ){
        return false;
    }
    $url = YH_UrlDecode($r['xoauth_request_auth_url']);
    $data = array();
    $data['url'] = $url;
    $data['requestTokenSecret'] = $r['oauth_token_secret'];
    return $data;
}

/**
 * @brief YH_AllInOne
 *
 * @param $requestTokenSecret
 *
 * @return
 */
function YH_AllInOne($requestTokenSecret){
    $r = YH_GetAccessToken($requestTokenSecret);
    if(!array_key_exists("xoauth_yahoo_guid", $r) ||
        !array_key_exists("oauth_token", $r) ||
        !array_key_exists("oauth_token_secret", $r)
    ){
        return false;
    }
    $guid = $r['xoauth_yahoo_guid'];
    $accessToken = $r['oauth_token'];
    $accessTokenSecret = $r['oauth_token_secret'];
    // because access token already urlencode by yahoo
    // so first decode it
    $r = YH_GetUser($guid, YH_UrlDecode($accessToken), $accessTokenSecret);
    return $r;
}

/**
 * @brief YH_GetRequestToken
 *
 * https://api.login.yahoo.com/oauth/v2/get_request_token
 *
 * about xoauth_lang_pref link:
 * http://oauth.googlecode.com/ \
 * svn/spec/ext/language_preference/1.0/drafts/2/spec.html
 *
 * about 401
 * http://stackoverflow.com/questions/ \
 * 6763187/omniauth-yahoo-error-oauthunauthorized-401-forbidden
 *
 * for later: get user authorization
 * https://api.login.yahoo.com/oauth/v2/request_auth
 *
 * @return
 */
function YH_GetRequestToken(){
    $url = "https://api.login.yahoo.com/oauth/v2/get_request_token";
    $nonce = md5(mt_rand());
    $timestamp = time();
    $params = array(
        "oauth_consumer_key"     => YAHOO_CONSUMER_KEY,
        "oauth_nonce"            => $nonce,
        "oauth_signature_method" => "HMAC-SHA1",
        "oauth_timestamp"        => $timestamp,
        "oauth_version"          => "1.0",
        //"xoauth_lang_pref"       => "en-us",
        "oauth_callback"         => YAHOO_REDIRECT_URI,
    );
    $b = YH_MakeSignatureBaseString("POST", $url, $params);
    $key = YAHOO_CONSUMER_SECRET. '&';
    $s = YH_Signature($b, $key);
    $params['oauth_signature'] = $s;
    $data = X_POST($url, $params);
    $r = explode("&", $data);
    $data = array();
    foreach($r as $v){
        $rr = explode("=", $v);
        $data[$rr[0]] = $rr[1];
    }
    return $data;
}

/**
 * @brief YH_GetAccessToken in callback
 *
 * @param $requestTokenSecret request token secret
 *
 * @return
 */
function YH_GetAccessToken($requestTokenSecret){
    if(!array_key_exists("oauth_token", $_GET) ||
        !array_key_exists("oauth_verifier", $_GET)){
            return false;
        }
    $oauthToken = $_GET['oauth_token'];
    $oauthVerifier = $_GET['oauth_verifier'];
    $url = "https://api.login.yahoo.com/oauth/v2/get_token";
    $nonce = md5(mt_rand());
    $timestamp = time();
    $params = array(
        "oauth_consumer_key"     => YAHOO_CONSUMER_KEY,
        "oauth_nonce"            => $nonce,
        "oauth_signature_method" => "HMAC-SHA1",
        "oauth_timestamp"        => $timestamp,
        "oauth_version"          => "1.0",
        "oauth_token"            => $oauthToken,
        "oauth_verifier"         => $oauthVerifier,
    );
    $b = YH_MakeSignatureBaseString("POST", $url, $params);
    $key = YAHOO_CONSUMER_SECRET. '&' . $requestTokenSecret;
    $s = YH_Signature($b, $key);
    $params['oauth_signature'] = $s;
    $data = X_POST($url, $params);
    $r = explode("&", $data);
    $data = array();
    foreach($r as $v){
        $rr = explode("=", $v);
        $data[$rr[0]] = $rr[1];
    }
    return $data;
}

/**
 * @brief YH_GetUser
 *
 * about realm
 * http://oauth.net/core/1.0 5.4, 9.1
 *
 * @param $guid
 * @param $accessToken
 * @param $accessTokenSecret
 *
 * @return
 */
function YH_GetUser($guid, $accessToken, $accessTokenSecret){
    $url = sprintf("http://social.yahooapis.com/v1/user/%s/profile", $guid);
    $nonce = md5(mt_rand());
    $timestamp = time();
    $params = array(
        "oauth_consumer_key"     => YAHOO_CONSUMER_KEY,
        "oauth_nonce"            => $nonce,
        "oauth_signature_method" => "HMAC-SHA1",
        "oauth_timestamp"        => $timestamp,
        "oauth_version"          => "1.0",
        "oauth_token"            => $accessToken,
        "format"                 => "json",
        );
    $b = YH_MakeSignatureBaseString("GET", $url, $params);
    $key = YAHOO_CONSUMER_SECRET. '&' . $accessTokenSecret;
    $s = YH_Signature($b, $key);
    $params['oauth_signature'] = $s;
    $data = X_GET($url, $params);
    $r = json_decode($data, true);

    $data = array();
    $data['user_id'] = $r['profile']['guid'];
    $data['name'] = sprintf("%s %s", $r['profile']['givenName'], $r['profile']['familyName']);
    foreach($r['profile']['emails'] as $v){
        if(array_key_exists("primary", $v) &&
            $v['primary'] == 1
        ){
            $data['email'] = $v['handle'];
            break;
        }
    }
    return $data;
}

/**
 * @brief YH_MakeSignatureBaseString
 * http://oauth.net/core/1.0/ section 9.1
 *
 * @param $method
 * @param $url
 * @param $params
 *
 * @return
 */
function YH_MakeSignatureBaseString($method, $url, $params){
    $separater = "&";

    $ba = array();
    $ba[] = strtoupper($method);
    $ba[] = YH_UrlEncode($url);
    $pa = array();
    ksort($params);
    foreach($params as $k=>$v){
        if (is_array($v)){
            $vs = $v;
            sort($vs);
            foreach ($vs as $vv){
                $pa[] = YH_UrlEncode($k).'='.YH_UrlEncode($vv);
            }
        }else{
            $pa[] = YH_UrlEncode($k).'='.YH_UrlEncode($v);
        }
    }
    $ba[] = YH_UrlEncode(implode($separater, $pa));
    $b = implode($separater, $ba);
    return $b;
}

/**
 * @brief YH_Signature
 * http://oauth.net/core/1.0 section 9.2
 *
 * @param $baseString
 *
 * @return
 */
function YH_Signature($baseString, $key){
    $s = hash_hmac("sha1", $baseString, $key, true);
    $s = base64_encode($s);
    return $s;
}

/**
 * @brief _YH_Signature  if can support hash_hmac func then use this
 *
 * @param $baseString
 * @param $token
 *
 * link: https://code.google.com/p/oauth-php/source/ \
 * browse/trunk/library/signature_method/OAuthSignatureMethod_HMAC_SHA1.php
 *
 * @return
 */
function _YH_Signature($baseString, $key){
    $blocksize  = 64;
    $hashfunc   = 'sha1';
    if (strlen($key) > $blocksize){
        $key = pack('H*', $hashfunc($key));
    }
    $key        = str_pad($key,$blocksize,chr(0x00));
    $ipad       = str_repeat(chr(0x36),$blocksize);
    $opad       = str_repeat(chr(0x5c),$blocksize);
    $hmac       = pack(
        'H*',$hashfunc(
            ($key^$opad).pack(
                'H*',$hashfunc(
                    ($key^$ipad).$baseString
                )
            )
        )
    );
    $signature = base64_encode($hmac);
    return $signature;
}

/**
 * Encode a string according to the RFC3986
 *
 * @param string s
 * @return string
 */
function YH_UrlEncode($s){
    return str_replace('%7E', '~', rawurlencode($s));
}

/**
 * Decode a string according to RFC3986.
 * Also correctly decodes RFC1738 urls.
 *
 * @param string s
 * @return string
 */
function YH_UrlDecode($s){
    return rawurldecode($s);
}

