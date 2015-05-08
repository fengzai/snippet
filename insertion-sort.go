//
// 插入排序
// 空间复杂度: O(1)
// 时间复杂度: O(n^2)
// https://zh.wikipedia.org/wiki/%E6%8F%92%E5%85%A5%E6%8E%92%E5%BA%8F
//
// cloud@txthinking.com
//
package main

import (
    "fmt"
)

var a []int = []int{8,3,4,2,8,5,10}

func main(){
    fmt.Println(a)

    var i,j int
    for i=1;i<len(a);i++{
        tmp := a[i]
        for j=i-1;j>=0;j--{
            if a[j] > tmp{
                a[j+1] = a[j]
            }else{
                break
            }
        }
        a[j+1] = tmp
        fmt.Println(a)
    }

    fmt.Println(a)
}
