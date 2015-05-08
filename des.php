<?php
/**
 * @file des.php
 * @brief DES 加密字符串
 * @author cloud@txthinking.com
 * @version 0.0.1
 * @date 2013-10-28
 */
/**
 * example:

// 加密
$a = DES_Encrypt("he啊啊啊llo@mai套需阿aaaaa iafsf  里l.com,{,ss}");
print $a . "\n";
// 解密
$b = DES_Decrypt($a);
print $b . "\n";

 */

define("DES_KEY", "GOGOWDHL");

/**
 * @brief DES_Encrypt 加密字符串
 *
 * @param $string
 * @param $key
 *
 * @return
 */
function DES_Encrypt($string, $key = DES_KEY) {
    $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
    $pad = $size - (strlen($string) % $size);
    $string = $string . str_repeat(chr($pad), $pad);
    $td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
    $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    @mcrypt_generic_init($td, $key, $iv);
    $data = mcrypt_generic($td, $string);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    $data = base64_encode($data);
    return $data;
}

/**
 * @brief DES_Decrypt 解密
 *
 * @param $string
 * @param $key
 *
 * @return
 */
function DES_Decrypt($string, $key = DES_KEY) {
    $string = base64_decode($string);
    $td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
    $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    $ks = mcrypt_enc_get_key_size($td);
    @mcrypt_generic_init($td, $key, $iv);
    $decrypted = mdecrypt_generic($td, $string);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    $pad = ord($decrypted{strlen($decrypted) - 1});
    if($pad > strlen($decrypted)) {
        return false;
    }
    if(strspn($decrypted, chr($pad), strlen($decrypted) - $pad) != $pad) {
        return false;
    }
    $result = substr($decrypted, 0, -1 * $pad);
    return $result;
}

