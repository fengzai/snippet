// 猜数游戏
//
// cloud@txthinking.com
package main

func main() {
	min, max := 0, 100
	fmt.Printf("Please think a integer between %d and %d\n", min, max)
	for min < max {
		i := (min + max) / 2
		fmt.Printf("The integer whether >= %d(y/n)", i)
		var s string
	    fmt.Scanf("%s", &s)
		if s != "" && s[0] == 'y' {
			min = i
		} else {
			max = i - 1
		}
	}
	fmt.Printf("The integer is %d\n", max)
}
