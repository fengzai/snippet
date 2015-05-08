/*=============================================================================
#     FileName: dynamicDns.go
#         Desc:
#       Author: Cloud Tao
#        Email: cloud@txthinking.com
#      Version: 0.0.1
#   LastChange: 2013-01-01 19:11:03
#      History:
=============================================================================*/

package main

import (
    "net/http"
    "io/ioutil"
    "bytes"
    "fmt"
    "encoding/base64"
)

func main(){
    username := "txthinking"
    password := "284339473"
    domain := "txthinking.vicp.cc"
    ip := getIp()
    r := dns(username, password, domain, ip)
    fmt.Println(r)
}

func getIp() string{
    resp, _ := http.Get("http://ifconfig.cc")
    defer resp.Body.Close()
    body, _ := ioutil.ReadAll(resp.Body)
    var buf *bytes.Buffer = bytes.NewBuffer(body)
    bufS := buf.String()
    return bufS
}

func dns(username string, password string, domain string, ip string) string{
    //base64
    Authorization := username+":"+password
    encodeStd := "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"
    var en *base64.Encoding = base64.NewEncoding(encodeStd)
    var buf *bytes.Buffer = bytes.NewBufferString(Authorization)
    enS := en.EncodeToString(buf.Bytes())

    //http request and header
    url := fmt.Sprintf("http://ddns.oray.com/ph/update?hostname=%s&myip=%s", domain, ip)
    requ, _ := http.NewRequest("GET", url, nil)
    requ.Header.Add("Authorization", "Basic "+enS)
    requ.Header.Add("User-Agent", "FUCKALL")

    client := new (http.Client)
    resp, _ := client.Do(requ)
    defer resp.Body.Close()

    body, _ := ioutil.ReadAll(resp.Body)
    buf = bytes.NewBuffer(body)
    bufS := buf.String()
    return bufS
}

