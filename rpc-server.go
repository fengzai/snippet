// tcp json rcp
// cloud@txthinking.com
package main

import(
    "net"
    "net/rpc"
    "net/rpc/jsonrpc"
    "errors"
    "log"

    "net/http"
    _ "net/http/pprof" // http://192.168.2.9:9109/debug/pprof/
)

type Args struct {
    A int `json:"a"`
    B int `json:"b"`
}

type Data struct {
    C int `json:"c"`
    D int `json:"d"`
    E string `json:"e"`
}

type Arith int

func (t *Arith) Plus(args *Args, data *Data) error {
    if args.B == 0 {
        return errors.New("divide by zero")
    }
    data.C = args.A + args.B
    data.D = args.A - args.B
    data.E = `
111111
`
    return nil
}

func Server(){
    var err error
    var l net.Listener
    l, err = net.Listen("tcp", ":1234")
    if err != nil {
        log.Fatal("listen error:", err)
    }
    defer l.Close()
    var s *rpc.Server
    s = rpc.NewServer()
    s.Register(new(Arith))
    for{
        var err error
        var c net.Conn
        c, err = l.Accept()
        log.Println(c.RemoteAddr())
        if err != nil {
            log.Fatal("listen error:", err)
        }
        go s.ServeCodec(jsonrpc.NewServerCodec(c))
    }
}

func main(){
    go func(){err := http.ListenAndServe("0.0.0.0:9109", nil)
        if err != nil {
            log.Println(err.Error())
        }
    }()
    Server()
}

