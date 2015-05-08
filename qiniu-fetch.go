//
// cloud@txthinking.com
package main

import(
    "errors"
    "crypto/hmac"
    "crypto/sha1"
    "io"
    "hash"
    "encoding/base64"
    "bytes"
    "net/http"
    "fmt"
    "log"
)

// http://developer.qiniu.com/docs/v6/api/reference/security/access-token.html
func MakeAccessToken(uri string, body string, accessKey string, secretKey string)(s string){
    var h hash.Hash
    h = hmac.New(sha1.New, bytes.NewBufferString(secretKey).Bytes())
    io.WriteString(h, uri+"\n"+body)
    s = base64.URLEncoding.EncodeToString(h.Sum(nil))
    s = accessKey+":"+s
    return
}

func Do(url string, bucket string, key string, accessKey string, secretKey string)(err error){
    var fetchURI, body, accessToken string
    var client *http.Client
    var request *http.Request
    var response *http.Response

    fetchURI = fmt.Sprintf("/fetch/%s/to/%s", base64.URLEncoding.EncodeToString(bytes.NewBufferString(url).Bytes()), base64.URLEncoding.EncodeToString(bytes.NewBufferString(bucket+":"+key).Bytes()))
    accessToken = MakeAccessToken(fetchURI, body, accessKey, secretKey)

    client = &http.Client{}
    request, err = http.NewRequest("POST", "http://iovip.qbox.me" + fetchURI, nil)
    if err != nil {
        return
    }
    request.Header.Add("Authorization", "QBox "+accessToken)
    response, err = client.Do(request)
    if err != nil {
        return
    }
    if response.StatusCode != http.StatusOK{
        err = errors.New(response.Status)
    }
    return
}

func main(){
    var err error
    var url, bucket, key, accessKey, secretKey string

    accessKey = ""
    secretKey = ""
    bucket = ""

    key = "sb.jpg"
    url = "http://www.baidu.com/img/bdlogo.png"
    err = Do(url, bucket, key, accessKey, secretKey)
    if err != nil{
        log.Println(err)
    }
}
