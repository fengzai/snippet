<?php
/**
 * @file cookie.php
 * @brief 设置cookie
 * @author cloud@txthinking.com
 * @version 0.0.1
 * @date 2013-10-28
 */

require_once ("des.php");

/**
 * @brief CK_Get 获取cookie
 *
 * @param $key
 *
 * @return
 */
function CK_Get($key){
    if(!array_key_exists($key, $_COOKIE)){
        return false;
    }
    return DES_Decrypt($_COOKIE[$key]);
}

/**
 * @brief CK_Set 设置cookie
 * old browser need a "." before domain
 * but i fuck it, so no point
 *
 * @param $key
 * @param $value
 *
 * @return
 */
function CK_Set($key, $value){
    $d = explode(".", $_SERVER['SERVER_NAME']);
    $l = count($d);
    $domain = sprintf("%s.%s", $d[$l-2], $d[$l-1]);
    $value = DES_Encrypt($value);
    return setcookie($key, $value, time()+30*60,
        "/", $domain, false, true);
}

/**
 * @brief CK_Delete 删除cookie
 *
 * @param $key
 *
 * @return
 */
function CK_Delete($key){
    return setcookie($key, "", time()-30*60);
}
