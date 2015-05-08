//
// cloud@txthinking.com
package main

import (
	"strconv"
	"net"
	"fmt"
	"bufio"
	"io"
	"strings"
	"regexp"
    "crypto/tls"
)

func main(){
	var userName string = ""
	var password string = ""
	var i int = 0;
	var t string = "TX"
	var tag string
	var nc net.Conn //interface type
	var err error
	var in string
	var out string
	var ok bool

	/*nc, err = net.Dial("tcp", "imap.163.com:143")*/
	nc, err = tls.Dial("tcp", "imap.163.com:993", nil)
	if err != nil {
		fmt.Printf("E")
		return
	}else{
		defer nc.Close()
	}

	i++
	tag = t + strconv.Itoa(i)
	in = tag + " CAPABILITY\r\n"
	if _, ok = getCode(nc, in, tag); !ok{
		return
	}

	i++
	tag = t + strconv.Itoa(i)
	in = tag + " LOGIN " + userName + " " +password+"\r\n"
	if _, ok = getCode(nc, in, tag); !ok {
		return
	}

	i++
	tag = t + strconv.Itoa(i)
	in = tag + " STATUS inbox (UNSEEN)\r\n"
	if out, ok = getCode(nc, in, tag); !ok {
		return
	}

	i++
	tag = t + strconv.Itoa(i)
	in = tag + " LOGOUT\r\n"
	if _, ok = getCode(nc, in, tag); !ok {
		return
	}

	var r string = `\(UNSEEN (\d+)\)`
	rr, _ := regexp.Compile(r)
	var ss [][]string = rr.FindAllStringSubmatch(out, 1)
	var unseen string = ss[0][1]

    unseenNumber, _  :=  strconv.Atoi(unseen)
    if unseenNumber > 0{
        //getmail
        /*cmd := exec.Command("getmail")*/
        /*cmd.Run()*/

        //sound
        cmd := exec.Command("aplay", "/home/tx/Music/gmail-sound.wav", "2>/dev/null")
        cmd.Run()
    }
}

func getCode(nc net.Conn, in string, tag string) (string, bool){
	var out string = ""
	yn := false

	fmt.Fprintf(nc, in)
	br := bufio.NewReader(nc)
	for {
		line, err := br.ReadString('\n')
		if err==io.EOF {
			break
		}
		out += line
		if strings.Contains(line, tag + " OK"){
			yn = true
			break
		}
		if strings.Contains(line, tag + " NO"){
			yn = false
			break
		}
		if strings.Contains(line, tag + " BAD"){
			yn = false
			break
		}
	}
	return out, yn
}

