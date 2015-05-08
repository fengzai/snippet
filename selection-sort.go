//
// 选择排序
// 空间复杂度: O(1)
// 时间复杂度: O(n^2)
// https://zh.wikipedia.org/wiki/%E9%80%89%E6%8B%A9%E6%8E%92%E5%BA%8F
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
    for i=0;i<len(a)-1;i++{
        min := i
        for j=i+1;j<len(a);j++{
            if a[j] < a[min]{
                min = j
            }
        }
        if min != i{
            a[i],a[min] = a[min],a[i]
        }
        fmt.Println(a)
    }

    fmt.Println(a)
}
