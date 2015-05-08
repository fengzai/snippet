<?php
/**
 * @file xhttp.php
 * @brief 主要用于发送google yahoo facebook http请求
 * @author cloud@txthinking.com
 * @version 0.0.1
 * @date 2013-10-23
 */

/**
 * @brief X_GET 将参数urlencode后发送GET请求
 *
 * @param $url
 * @param $params
 * @param $header 参考php curl函数
 *
 * @return
 */
function X_GET($url, $params, $header){
    $url = $url . "?";
    if(!is_array($params)){
        $params = array();
    }
    $pa = array();
    foreach($params as $k=>$v){
        $k = rawurlencode($k);
        $v = rawurlencode($v);
        $pa[] = sprintf("%s=%s", $k, $v);
    }
    $url .= implode("&", $pa);
    $request = curl_init();
    $xArray = array(
        CURLOPT_URL            => $url,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
    );
    if(is_array($header)){
        $xArray[CURLOPT_HTTPHEADER] = $header;
    }
    curl_setopt_array($request, $xArray);
    $data = curl_exec($request);
    curl_close($request);
    return $data;
}

/**
 * @brief X_POST 发送Content-Type: multipart/form-data POST请求
 *
 * @param $url
 * @param $params
 * @param $header 参考php curl函数
 *
 * @return
 */
function X_POST($url, $params, $header){
    // for header: Content-Type: multipart/form-data
    if(!is_array($params)){
        $params = array();
    }
    // for header: Content-Type: application/x-www-form-urlencoded
    //$pa = array();
    //foreach($params as $k=>$v){
        //$k = urlencode($k);
        //$v = urlencode($v);
        //$pa[] = sprintf("%s=%s", $k, $v);
    //}
    //$params = implode("&", $pa);
    $request = curl_init();
    $xArray = array(
        CURLOPT_URL            => $url,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $params,
    );
    if(is_array($header)){
        $xArray[CURLOPT_HTTPHEADER] = $header;
    }
    curl_setopt_array($request, $xArray);
    $data = curl_exec($request);
    curl_close($request);
    return $data;
}

