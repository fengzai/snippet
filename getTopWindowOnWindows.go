// 获取Windows当前窗口并给另外一个机器发通知
// cloud@txthinking.com
//
package main

import (
    "syscall"
    "unsafe"
    "net"
    "time"
    "bufio"
    "io"
    "fmt"
)

var (
    moduser32 = syscall.NewLazyDLL("user32.dll")

    procGetForegroundWindow = moduser32.NewProc("GetForegroundWindow")
    procGetClassNameW       = moduser32.NewProc("GetClassNameW")
)

func GetForegroundWindow() (hwnd syscall.Handle, err error) {
    r0, _, e1 := syscall.Syscall(procGetForegroundWindow.Addr(), 0, 0, 0, 0)
    if e1 != 0 {
        err = error(e1)
        return
    }
    hwnd = syscall.Handle(r0)
    return
}

func GetClassName(hwnd syscall.Handle) (name string, err error) {
    n := make([]uint16, 256)
    p := &n[0]
    r0, _, e1 := syscall.Syscall(procGetClassNameW.Addr(), 3, uintptr(hwnd), uintptr(unsafe.Pointer(p)), uintptr(len(n)))
    if r0 == 0 {
        if e1 != 0 {
            err = error(e1)
        } else {
            err = syscall.EINVAL
        }
        return
    }
    name = syscall.UTF16ToString(n)
    return
}

func TellMe(){
    nc, _ := net.Dial("tcp", "192.168.2.9:13000")
    fmt.Fprintf(nc, "HI\n")
    br := bufio.NewReader(nc)
    for{
        line, err := br.ReadString('\n')
        if err==io.EOF {
            break
        }
        if line == "OVER\n"{
            break
        }
    }
    nc.Close()
}

func main (){
    var last string
    for{
        func(){
            h,_ := GetForegroundWindow()
            r,_ := GetClassName(h)
            if len(r) <= 0 {
                return
            }
            if r[0:1] == "#"{
                if r != last{
                    TellMe()
                    last = r
                }
            }else{
                last = r
            }
        }()
        time.Sleep(time.Second)
    }
}
