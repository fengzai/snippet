//
// cloud@txthinking.com
package main

import (
    "fmt"
    "net"
    "bufio"
    "io"
    "os/exec"
)

func main(){
    ln, err := net.Listen("tcp", "192.168.2.9:13000")
    if err != nil {
        // handle error
    }
    for {
        conn, err := ln.Accept()
        if err != nil {
            // handle error
            continue
        }
        go func(conn net.Conn){
            br := bufio.NewReader(conn)
            for{
                line, err := br.ReadString('\n')
                if err==io.EOF {
                    break
                }
                if line == "HI\n"{
                    fmt.Fprintf(conn, "OVER\n")
                    cmd := exec.Command("aplay", "/home/tx/Music/ao.wav", "2>/dev/null")
                    cmd.Run()
                    break
                }
            }
            conn.Close()
        }(conn)
    }
}

