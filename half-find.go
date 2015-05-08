// 二分查找
// 空间复杂度O(1)
// 时间复杂度O(log n)
// https://zh.wikipedia.org/wiki/%E4%BA%8C%E5%88%86%E6%9F%A5%E6%89%BE%E6%B3%95
//
// cloud@txthinking.com

package main

import (
)

var a []int = []int{1,3,4,5,8,9,10}
var f int = 8

func main(){
    var min,max int = 0,len(a)
    for{
        if min > max{
            println("Not Found");
            return
        }

        var mid int = min+(max-min)/2 // Do not use (low+high)/2 which might encounter overflow issue. Maybe Very Big. HAHA
        if a[mid] == f{
            println("Found Index:", mid)
            return
        } else if a[mid] > f{
            max = mid
        } else{
            min = mid
        }

    }
}
