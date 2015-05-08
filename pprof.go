package main

import (
    "net/http"
    _ "net/http/pprof" // http://192.168.2.9:9109/debug/pprof/
	"fmt"
    "runtime/pprof" // for not web application
    "os"
)

func proxy(w http.ResponseWriter, r *http.Request) {
	w.WriteHeader(http.StatusOK)
	w.Write([]byte("hello"))
}

func main() {
    var err error
    var f *os.File
    f, err  = os.Create("/tmp/pprof")
    if err != nil {
        fmt.Println(err.Error())
    }
    err = pprof.StartCPUProfile(f)
    if err != nil {
        fmt.Println(err.Error())
    }
    defer pprof.StopCPUProfile()
    fmt.Println("[INFO] Begin")
	http.HandleFunc("/", proxy)
	err = http.ListenAndServe("0.0.0.0:9109", nil)
    if err != nil {
        fmt.Println(err.Error())
    }
    fmt.Println("[INFO] End")
}

