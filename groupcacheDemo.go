//
// groupcache demo
//
// Author cloud@txthinking.com
//
package main

import (
    "log"
    "github.com/golang/groupcache"
    "net/http"
    "time"
)

var p *groupcache.HTTPPool
var g *groupcache.Group

func main() {
    var err error
    p = groupcache.NewHTTPPool("http://127.0.0.1:50000")
    // update p's list
    p.Set("http://127.0.0.1:50001", "http://127.0.0.1:50002")

    // create a new group by name and set a function for get a value by key
    // if you g.Get KEY at first time, it will store the value use sink, run the function
    // if you g.Get KEY not at first time, actually it NOT entry the function
    // does not support update a key's value
    g = groupcache.NewGroup("dns", 64<<20, groupcache.GetterFunc(
        func(ctx groupcache.Context, key string, dest groupcache.Sink) (err error) {
            if ctx == nil {
                ctx = "fuck"
            }
            log.Println(key, ctx)
            err = dest.SetString(ctx.(string))
            return
        }))

    // let it listen
    go http.ListenAndServe("127.0.0.1:50000", nil)

    // get a key's value into data

    time.Sleep(2 * time.Second)
    var data []byte
    var key = "key"
    err = g.Get("aa", key, groupcache.AllocatingByteSliceSink(&data))
    if err != nil {
        log.Println("get error:", err)
    }
    log.Println(string(data))

    time.Sleep(2 * time.Second)
    key = "key1"
    err = g.Get("bb", key, groupcache.AllocatingByteSliceSink(&data))
    if err != nil {
        log.Println("get error:", err)
    }
    log.Println(string(data))

    time.Sleep(2 * time.Second)
    key = "key"
    err = g.Get("cc", key, groupcache.AllocatingByteSliceSink(&data))
    if err != nil {
        log.Println("get error:", err)
    }
    log.Println(string(data))
}

