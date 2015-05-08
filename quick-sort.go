//
// 快速排序
// 时间复杂度: O(n^2)
// https://zh.wikipedia.org/wiki/%E5%BF%AB%E9%80%9F%E6%8E%92%E5%BA%8F
//
// cloud@txthinking.com
//
package main

import(
    "fmt"
)

func qsort(data []int) {
    if len(data) <= 1 {
        return
    }
    mid, i := data[0], 1
    head, tail := 0, len(data)-1
    for head < tail {
        if data[i] > mid {
            data[i], data[tail] = data[tail], data[i]
            tail--
        } else {
            data[i], data[head] = data[head], data[i]
            head++
            i++
        }
    }
    data[head] = mid
    qsort(data[:head])
    qsort(data[head+1:])
}

var a []int = []int{8,3,4,2,8,5,10}

func main (){
    fmt.Println(a)
    qsort(a)
    fmt.Println(a)
}

