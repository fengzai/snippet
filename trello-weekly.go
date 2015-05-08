/**
* @file weekly.go
* @brief
* @author cloud@txthinking.com
* @version 0.0.1
* @date 2013-07-30
*/
/*
API
* get my key and secret
visit: https://trello.com/1/appKey/generate

* get forever readable token
visit: https://trello.com/1/authorize?key=[my key]&name=[My Application Name]&expiration=never&response_type=token

* get all list on a board
https://api.trello.com/1/boards/[board short id]?key=[my key]&token=[my token]&lists=open

* get all cards in a list
https://api.trello.com/1/lists/[list id]?key=[my key]&token=[my token]&cards=open

* get a member id
https://api.trello.com/1/members/[member username or member id]?key=[my key]&token=[my token]

* get a member information about boards lists cards
https://api.trello.com/1/members/[member username or member id]?key=[my key]&token=[my token]&boards=open&board_lists=open&cards=open
*/
package main

import (
    "net/http"
    "io/ioutil"
    "fmt"
    "encoding/json"
    "net/smtp"
    "bytes"
    "io"
    "os"
    "time"
    /*"crypto/tls"*/
)

const (
    KEY           = ""
    SECRET        = ""
    FOREVER_TOKEN = ""
    BOARD_ID      = "51cbdbaa840e11220d003a0d"
    LIST_ID       = "51cbdbaa840e11220d003a10"
    LIST_ID_DOING = "51cbdbaa840e11220d003a0f"
)

type Member struct {
    UserName string // trello user name
    Name string
    Email string // for From
}

type Label struct {
    Color string
    Name string
}

type Card struct {
    IdBoard string
    IdList string
    Labels []Label
    Name string
    Desc string
    Due string
}

type CardSlice struct {
    Cards []Card
}

// entry
func main() {
    var data []byte
    var body string
    var members []*Member
    var member *Member
    var err error

    members, err = GetMembers("/home/tx/workspace/tx/go/scripts/weekly.json")
    if err != nil{
        fmt.Printf("[ERROR] %s\n", err.Error())
        return
    }
    for _, member = range members{
        data, err = member.GetData()
        if err != nil{
            fmt.Printf("[ERROR] %s\n", err.Error())
            return
        }
        body, err = ParseData(data)
        if err != nil{
            fmt.Printf("[ERROR] %s\n", err.Error())
            return
        }
        // fmt.Print(body)
        // return
        err = member.SendMail(body)
        if err != nil{
            fmt.Printf("[ERROR] %s\n", err.Error())
            return
        }
    }
}

// get members config
// need a json file
func GetMembers(configFile string) (members []*Member, err error){
    var file *os.File
    var data []byte
    file, err = os.OpenFile(configFile, os.O_RDONLY, 0444)
    if err != nil {
        return
    }
    data, err = ioutil.ReadAll(file)
    if err != nil {
        return
    }
    err = json.Unmarshal(data, &members)
    if err != nil {
        return
    }
    return
}

// get data from trello api
func (member *Member) GetData()(body []byte, err error){
    var url string
    var res *http.Response
    url = fmt.Sprintf("https://api.trello.com/1/members/%s?key=%s&token=%s&boards=open&board_lists=open&cards=open", member.UserName, KEY, FOREVER_TOKEN)
    res, err = http.Get(url)
    if err != nil {
        return
    }
    body, err = ioutil.ReadAll(res.Body)
    if err != nil {
        return
    }
    res.Body.Close()
    return
}

// parse data which is from trello api to html for mail
func ParseData(data []byte)(body string, err error){
    var cards CardSlice
    var card Card
    var label Label
    var location *time.Location
    var html string
    var bodyDoing string
    err = json.Unmarshal(data, &cards)
    if err != nil {
        return
    }
    for _, card = range cards.Cards{
        if card.IdBoard != BOARD_ID{
            continue
        }
        if card.IdList != LIST_ID && card.IdList != LIST_ID_DOING{
            continue
        }
        if card.Due != ""{
            var dueTime time.Time
            location, err = time.LoadLocation("UTC")
            if err != nil {
                return
            }
            dueTime, err = time.ParseInLocation(time.RFC3339, card.Due, location)
            if err != nil {
                return
            }
            location, err = time.LoadLocation("Asia/Hong_Kong")
            if err != nil {
                return
            }
            card.Due = dueTime.In(location).Format("2006-01-02 15:04:05")
        }
        var labels string
        for _, label = range card.Labels{
            labels += fmt.Sprintf("[%s]", label.Name)
        }
        if labels == "" {
            labels = "[No Label]"
        }
        if card.IdList == LIST_ID{
            html = `<li>
<strong>%s</strong><br/>
<b>结束</b> %s <b>标签</b> %s<br/>
<pre>%s</pre>
</li>
`
            body += fmt.Sprintf(html, card.Name, card.Due, labels, card.Desc)
        }
        if card.IdList == LIST_ID_DOING{
            html = `<li>
<strong>%s</strong><br/>
<pre>%s</pre>
</li>
`
            bodyDoing += fmt.Sprintf(html, card.Name, card.Desc)
        }
    }
    body = fmt.Sprintf("<h4>本周主要事项</h4><ul>%s</ul><h4>下周主要事项</h4><ul>%s</ul>", body, bodyDoing)
    return
}

// tls: use 587 instead of 465 of gmail, 465 is old
//
func (member *Member) SendMail(data string) (err error){
}



