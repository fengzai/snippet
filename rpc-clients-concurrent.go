//
// cloud@txthinking.com
package main

import(
    "net"
    "net/rpc"
    "net/rpc/jsonrpc"
    "log"
    "fmt"
)

type PlusA struct {
    A int `json:"A"`
    B int `json:"B"`
}

type PlusR struct {
    C int `json:"C"`
}

func Client(){
    var err error
    var c net.Conn
    c, err = net.DialTimeout("tcp", "127.0.0.1:9999", 1000*1000*1000*30)
    if err != nil {
        log.Fatal("dialing:", err)
    }

    var client *rpc.Client
    client = jsonrpc.NewClient(c)

    // 同步
    var a *PlusA = &PlusA{7,8}
    var r *PlusR = new(PlusR)

    ch := make(chan int)
    for i:=0;i<10000;i++{
        go func(){
            client.Call("Test.Plus", a, r)
            <-ch
        }()
    }

    for j:=0;j<10000;j++{
        ch<-1
        fmt.Println(r)
    }

    client.Close()
    c.Close()
}

func main(){
    Client()
}

