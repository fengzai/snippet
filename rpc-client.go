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

type Args struct {
    A int `json:"a"`
    B int `json:"b"`
}

type Data struct {
    C int `json:"c"`
    D int `json:"d"`
}

func Client(){
    var err error
    var c net.Conn
    c, err = net.DialTimeout("tcp", "127.0.0.1:1234", 1000*1000*1000*30)
    if err != nil {
        log.Fatal("dialing:", err)
    }

    var client *rpc.Client
    client = jsonrpc.NewClient(c)

    // 同步
    var args *Args = &Args{7,8}
    var reply *Data = new(Data)
    client.Call("Arith.Plus", args, reply)
    fmt.Println(reply)

    // 异步
    args.A = 1
    args.B = 2
    var call *rpc.Call
    call = client.Go("Arith.Plus", args, reply, nil)
    var doneCall *rpc.Call
    doneCall = <-call.Done
    fmt.Println(doneCall.Args, doneCall.Reply)
    fmt.Println(args, reply)

    client.Close()
    c.Close()
}

func main(){
    Client()
}

