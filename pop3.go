//
// cloud@txthinking.com
package main

import (
	"net"
	"fmt"
	"bufio"
	"io"
	"strings"
    "regexp"
	"strconv"
    "os/exec"
    /*"crypto/tls"*/
)

func main(){
	var userName string = ""
	var password string = ""
	var nc net.Conn //interface type
	var err error
	var in string
	var out string
	var ok bool

    nc, err = net.Dial("tcp", "x.com:110")
    /*nc, err = tls.Dial("tcp", "pop.163.com:995", nil)*/
	if err != nil {
		fmt.Printf("E")
		return
	}else{
		defer nc.Close()
	}

    //Welcome message
	_, _ = getCode(nc, "")

	in = "USER "+userName+"\r\n"
	if _, ok = getCode(nc, in); !ok{
		return
	}

	in = "PASS "+password+"\r\n"
	if _, ok = getCode(nc, in); !ok {
		return
	}

	in = "STAT\r\n"
	if out, ok = getCode(nc, in); !ok {
		return
	}
    /*fmt.Printf(out)*/

	in = "QUIT\r\n"
	if _, ok = getCode(nc, in); !ok {
		return
	}

    //get number
    var r string = `\+OK (\d+) .*`
    rr, _ := regexp.Compile(r)
    var ss [][]string = rr.FindAllStringSubmatch(out, 1)
    var unseen string = ss[0][1]

    unseenNumber, _  :=  strconv.Atoi(unseen)
    if unseenNumber > 0{
        //getmail
        cmd := exec.Command("getmail")
        cmd.Run()

        //sound
        //cmd = exec.Command("aplay", "/home/tx/Music/gmail-sound.wav", "2>/dev/null")
        //cmd.Run()
    }

}

func getCode(nc net.Conn, in string) (string, bool){
    out := ""
	yn := false

	fmt.Fprintf(nc, in)
	br := bufio.NewReader(nc)
	for {
		line, err := br.ReadString('\n')
		if err==io.EOF {
			break
		}
		out += line
		if strings.Contains(line, "+OK"){
			yn = true
			break
		}
		if strings.Contains(line, "-ERR"){
			yn = false
			break
		}
	}
	return out, yn
}

