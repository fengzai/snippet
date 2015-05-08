<?php
// 计算1的个数
//
// cloud@txthinking.com
/*
 * 根据十进制不断模除2得余数判断
 * 算法分析:
 * 一个数不断除以2得的余数倒序则为这个数的二进制
 * 那么n模除2如果余数为1则+1
 * 再用n/2继续模除2
 * 直到n=0
 */
function countOne($ten){
    $count = 0;
    while ($ten != 0){
        if ($ten%2 == 1){
            $count++;
        }
        $ten /= 2;
    }
    return $count;
}
/*
 * 右移位(每右移一次相当除以2), 最后移位与1想与
 * 算法分析:
 * 将n与1相与如果为1则n最后一位为1
 * 然后右移1位(最后一位被抛弃了)
 */
function countOne1($ten){
    $count = 0;
    while ($ten != 0){
        if ($ten&1){
            $count++;
        }
        $ten >>= 1;
    }
    return $count;
}
/*
 * 算法分析:
 * 如果n是2的整数次幂(里面肯定只有一个1)
 * 形式是n:1000... n-1:0111... n&(n-1)为0
 * n-1, n的每一次的-1都会启动最后位置的1去减
 * n-1的结果保留了启动1之前的所有位
 * n-1的结果使启动1本位变为0
 * n-1的结果使启动1之后的所有0全部为变为1
 * n&(n-1), 其实相与变化的部分仅为启动1位和其之后位(因为也只有这些变化了)
 * 每一次的n=n&(n-1)都能将n的最后一个1干掉
 * 每干掉一次+1
 * 直到n=0
 */
function countOne2($ten){
    $count = 0;
    while ($ten != 0){
        $ten &= $ten-1;
        $count++;
    }
    return $count;
}
