/*=============================================================================
#     FileName: gplayer.go
#         Desc: A terminal music player
#       Author: Cloud Tao
#        Email: cloud@txthinking.com
#      Version: 0.0.3
#   LastChange: 2012-12-03 10:02:23
#      History:
TODO 自解码
=============================================================================*/
package main

import(
    "flag"
    "fmt"
    "os"
    "os/exec"
    "bufio"
    "io"
    "strings"
    "time"
    "strconv"
    "path/filepath"
    "net/http"
    "io/ioutil"
    "net/url"
    "regexp"
)

/*prepare variable with flags*/
var l *string
var h *bool
var musics []string = make([]string, 0) //原始歌曲参数
var chanMusic chan string = make(chan string, 1) //用于控制音乐播放
var chanLrc chan map[string]string = make(chan map[string]string, 1) //用于控制歌词显示

type Music struct {
    name string
    path string
    pathLrc string
}

/*define flags, parse, init musics*/
func init(){
    h = flag.Bool("h", false, "help")
    l = flag.String("l", "", "music")
    flag.Parse()
    //是否是帮助
    if *h {
        func(){
            help :=
`
GPlayer is based on mplayer.
Usage: gplayer [-FLAG [VALUE]] [MUSIC [MUSIC]]
Flag:
-h      Show help
-l FILE Play a music list file
`
            fmt.Print(help)
            os.Exit(0)
        }()
    }
    //是否是播放列表
    if *l != ""{
        dir := filepath.Dir(*l)
        lst, _ := os.OpenFile(*l, os.O_RDONLY, 0444)
        defer lst.Close()
        bnr := bufio.NewReader(lst)
        for{
            line, err := bnr.ReadString('\n')
            if err==io.EOF {
                break
            }
            musics = append(musics, dir+"/"+line[0:len(line)-1])
        }
        return
    }
    //是否是连续的歌曲名参数
    musics = append(musics, flag.Args()...)
}

/*main*/
func main(){
    f := true //如果是第一次就开辟显示歌词的线程
    for i:=0;;i++ {
        if i==len(musics){
            i=0
        }
        Music := NewMusic(musics[i])

        /*chanMusic <- Music.name //传入歌曲, 实际只是个标志开始*/

        Music.checkLrc()
        lrcMap := Music.getIndexLrc()
        chanLrc <- lrcMap //传入当前歌曲歌词
        /*go func(){*/
            /*for{*/
                /*bnr := bufio.NewReader(os.Stdin)*/
                /*q, _ := bnr.ReadByte()*/
                /*if int(q)==113{*/
                    /*os.Exit(0)*/
                /*}*/
            /*}*/
        /*}()*/
        if f {
            go func (){
                /*显示歌词*/
                lrcMap := make(map[string]string)
                s := 0
                for{
                    select{
                    case lrcMap = <- chanLrc:
                        s = 0
                    default:
                        time.Sleep(time.Second)
                        s++
                        sm := s/60
                        ss := s%60
                        k := ""
                        if sm<10{
                            k += "0"+ strconv.Itoa(sm)
                        }else{
                            k += strconv.Itoa(sm)
                        }
                        k += ":"
                        if ss<10{
                            k += "0"+ strconv.Itoa(ss)
                        }else{
                            k += strconv.Itoa(ss)
                        }

                        if line, ok := lrcMap[k]; ok{
                            fmt.Print(line)
                        }
                    }
                }
            }()
        }

        f = false

        /*
        * 播放歌曲, 其会等待命令执行完成再继续
        */
        func (pathMusic string){
            /*播放歌曲*/
            cmd := exec.Command("mplayer", pathMusic)
            /*_ = cmd.Start()*/
            /*_ = cmd.Wait()*/
            cmd.Run()
            //读取chan 通知主程序进入下一个歌曲
            /*_ = <- chanMusic*/
        }(Music.path)
    }
}

/**
* get music names, music absolute pathes, lrc absolute pathes
*/
func NewMusic(music string) (*Music){
    Music := new (Music)
    musicAbs, _ := filepath.Abs(music)
    musicDir := filepath.Dir(musicAbs)
    musicFile := filepath.Base(music)
    musicName := musicFile[0:strings.LastIndex(musicFile,".")]
    lrcAbs := musicDir + "/" + musicName + ".lrc"
    Music.path = musicAbs
    Music.pathLrc = lrcAbs
    Music.name = musicName
    return Music
}

/**
* 检查歌词是否存在, 不存在则下载
*/
func (Music *Music)checkLrc(){
    lrc, err := os.OpenFile(Music.pathLrc, os.O_RDONLY, 0444)
    /*歌词存在*/
    if os.IsExist(err){
        lrc.Close()
    }
    /*歌词不存在则下载*/
    if os.IsNotExist(err) {
        /*从网络获取歌词*/
        fmt.Println("从网络下载歌词中...")
        vUrl := url.Values{}
        vUrl.Set("key", Music.name)
        urlLrc := "http://music.baidu.com/search/lrc?"+vUrl.Encode()
        response, _ := http.Get(urlLrc)
        body, _ := ioutil.ReadAll(response.Body)
        response.Body.Close()
        /*正则筛选查询的歌词列表*/
        bodyString := string(body)
        var r string = `<span class="song-title">[\S\s]*?title="(\S*?)"[\s\S]*?</span>[\s\S]*?<span class="artist-title">[\s\S]*?<span class="author_list">[\s\S]*?title="(\S*?)"[\s\S]*?</span>[\s\S]*?</span>[\s\S]*?<a class="down-lrc-btn { 'href':'(\S*?)' }" href="#">`
        rr, _ := regexp.Compile(r)
        var resultsSearch [][]string = rr.FindAllStringSubmatch(bodyString, 6)
        //resultsSearch[0]第一个匹配切片 resultsSearch[0][0]整体正则 resultsSearch[0][1]第一个括号 ...
        //1歌曲, 2歌首, 3歌词下载链接
        /*整理搜索结果, 并展示供选择*/
        resultsLrc := make([][]string , 6)
        for i, v := range resultsSearch{
            v[3] = "http://music.baidu.com"+v[3]
            resultsLrc[i] = v[1:]
            //展示选项
            fmt.Printf("%d\t歌曲: %s\t歌手: %s\n", i, resultsLrc[i][0], resultsLrc[i][1]);
        }
        /*选择歌词*/
        fmt.Printf("请选择歌词: ")
        bnr := bufio.NewReader(os.Stdin)
        numberString, _ := bnr.ReadString('\n')
        numberInt, _ := strconv.Atoi(numberString[0:1])
        /*下载歌词*/
        response, _ = http.Get(resultsLrc[numberInt][2])
        body, _ = ioutil.ReadAll(response.Body)
        response.Body.Close()
        /*保存歌词*/
        lrc, _ := os.OpenFile(Music.pathLrc, os.O_WRONLY|os.O_CREATE, 0644)
        _, _ = lrc.Write(body)
        lrc.Close()
    }
}

/**
* make index of lrc
*/
func (Music *Music) getIndexLrc()(map[string]string){
    lrc, _ := os.OpenFile(Music.pathLrc, os.O_RDONLY, 0444)
    defer lrc.Close()
    bnr := bufio.NewReader(lrc)
    var lrcMap = make(map[string]string)
    for{
        line, err := bnr.ReadString('\n')
        if err==io.EOF {
            break
        }
        geci := line[strings.LastIndex(line,"]")+1:]
        r := `\[(\d\d:\d\d)\.\d\d\]`
        rr, _ := regexp.Compile(r)
        var resultsSearch [][]string = rr.FindAllStringSubmatch(line, 9)
        //resultsSearch[0]第一个匹配切片 resultsSearch[0][0]整体正则 resultsSearch[0][1]第一个括号 ...
        for _, v := range resultsSearch{
            lrcMap[v[1]] = geci
        }
    }
    return lrcMap
}
