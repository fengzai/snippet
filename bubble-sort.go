// 二分查找
// 空间复杂度O(1)
// 时间复杂度O(n^2)
// https://zh.wikipedia.org/wiki/%E5%86%92%E6%B3%A1%E6%8E%92%E5%BA%8F
//
// cloud@txthinking.com
package main

import(
    "fmt"
)

var a []int = []int{8,3,4,2,8,5,10}

func main(){
    fmt.Println(a)
    var i,j int
    for i=0;i<len(a);i++{
        for j=0;j<len(a)-i-1;j++{
            if a[j]>a[j+1]{
                a[j+1],a[j] = a[j],a[j+1]
            }
        }
    }
    fmt.Println(a)
}
