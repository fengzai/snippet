// 找出数组中出现次数最多的元素
//
// cloud@txthinking.com

package main

import "fmt"

func main() {
	a := []int {1,1,1,1,1,2,2,2,2,2,2,2,3,3}

	//first method
	b := make([]int, len(a)+a[len(a)-1])
	for i:=0; i<len(a); i++ {

		b[a[i]]++
	}
	var maxV, maxC int
	for i:=0; i<len(b); i++{
		if b[i]>maxC {
			maxV,maxC = i, b[i]
		}
	}
	fmt.Println("值:", maxV, "次数:", maxC)

	//second method
	c := make(map[int]int)
	for _, v := range a{
		c[v]++
	}
	maxV, maxC = 0, 0
	for k,v := range c {
		if v > maxC {
			maxV, maxC = k, v
		}
	}
	fmt.Println("值:", maxV, "次数:", maxC)
}

