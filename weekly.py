#!/usr/bin/python
# -*- coding: utf-8 -*-
##
# @file weekly.py
# @brief weekly script, implement trello api
# @author cloud@txthinking.com
# @version 0.0.1
# @date 2013-7-30

"""
0. get my key and secret
visit: https://trello.com/1/appKey/generate

1. get forever token
visit: https://trello.com/1/authorize?\
        key=[my key]&name=[My Application Name]\
        &expiration=never&response_type=token
"""

import urllib
import json
import threading
import time
import os
import sys
import smtplib
import email.mime.multipart
import email.mime.text
import getopt

C = None

def usage():
    h = """
Usage:
    Short Flag
        -h 帮助信息
        -f /path/config.json     REQUIRE 配置文件路径
    Long Flag
        --help 帮助信息
        --file=/path/config.json  REQUIRE 配置文件路径
"""
    print h

def init():
    global C
    config = ""
    try:
        fa = getopt.getopt(sys.argv[1:], "hf:", ["help", "file="])
    except getopt.GetoptError:
        usage()
        sys.exit(2)
    for f, a in fa[0]:
        if f in ("-h", "--help"):
            usage()
            sys.exit(0)
        if f in ("-f", "--file"):
            config = a
            break
    if config == "":
        usage()
        sys.exit(2)
    C = json.load(open(config, "r"))

def getMembers():
    global C
    return C["users"]

def getData(m):
    global C
    url = "https://api.trello.com/1/members/%s"
    url += "?key=%s&token=%s&boards=open&board_lists=open&cards=open"
    url = url % (m["userName"].encode("utf-8"),
            C["KEY"].encode("utf-8"),
            C["FOREVER_TOKEN"].encode("utf-8"))
    f = urllib.urlopen(url)
    d = f.read()
    data = json.loads(d)
    return parseData(data)

def parseData(data):
    global C
    done, doing = "", ""
    for c in data["cards"]:
        if c["idBoard"] != C["BOARD_ID"].encode("utf-8"):
            continue
        if c["idList"] != C["LIST_ID_DONE"].encode("utf-8") and\
                c["idList"] != C["LIST_ID_DOING"].encode("utf-8"):
            continue
        if c["due"] != None:
            c["due"] = makeLocalTime(c["due"])
        else:
            c["due"] = " "
        labels = ""
        for l in c["labels"]:
            labels += "[%s]" % l["name"]
        html = """
<li>
<strong>%s</strong><br/>
<b>结束</b>%s<b>标签</b>%s<br/>
<pre>%s</pre>
</li>
"""
        if c["idList"] == C["LIST_ID_DONE"].encode("utf-8"):
            done += html % (
                    c["name"].encode("utf-8"),
                    c["due"].encode("utf-8"),
                    labels.encode("utf-8"),
                    c["desc"].encode("utf-8")
                    )
        if c["idList"] == C["LIST_ID_DOING"].encode("utf-8"):
            doing += html % (
                    c["name"].encode("utf-8"),
                    c["due"].encode("utf-8"),
                    labels.encode("utf-8"),
                    c["desc"].encode("utf-8")
                    )
    html = """
<h4>本周主要事项</h4>
<ul>%s</ul>
<h4>下周主要事项</h4>
<ul>%s</ul>
"""
    body = html % (done, doing)
    return body

def makeLocalTime(due):
    os.environ["TZ"] = "UTC"
    time.tzset()
    t = time.mktime(time.strptime(due, "%Y-%m-%dT%H:%M:%S.000Z"))
    os.environ["TZ"] = "Asia/Hong_Kong"
    time.tzset()
    r = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(t))
    return r

def sendMail(m, body):
    global C
    html = email.mime.text.MIMEText(body, "html", "utf-8")
    s = smtplib.SMTP(C["mail"]["smtpServer"].encode("utf-8"), 25)
    mi = email.mime.multipart.MIMEMultipart()
    mi.add_header('Return-Path', m["email"].encode("utf-8"))
    mi.add_header('From', "%s <%s>" % (m["name"].encode("utf-8"),
        m["email"].encode("utf-8")))
    tos = [m["email"].encode("utf-8")]
    for i in range(len(C["To"])):
        to = "%s <%s>" % (C["To"][i]["name"].encode("utf-8"),
                C["To"][i]["email"].encode("utf-8"))
        mi.add_header('To', to)
        tos.append(C["To"][i]["email"].encode("utf-8"))
    for i in range(len(C["Cc"])):
        cc = "%s <%s>" % (C["Cc"][i]["name"].encode("utf-8"),
                C["Cc"][i]["email"].encode("utf-8"))
        mi.add_header('Cc', cc)
        tos.append(C["Cc"][i]["email"].encode("utf-8"))
    mi.add_header('Subject',
            "[周报] 技术部网站组%s%s" % (time.strftime("%Y%m%d"),
                m["name"].encode("utf-8")))
    mi.attach(html)
    s.login(C["mail"]["userName"].encode("utf-8"),
            C["mail"]["password"].encode("utf-8"))
    #tos = ['tmp@ym.txthinking.com']
    s.sendmail(C["mail"]["userName"].encode("utf-8"), tos, mi.as_string())
    s.quit()

class T(threading.Thread):
    def __init__(self, m):
        threading.Thread.__init__(self)
        self.m = m

    def run(self):
        run(self.m)

def run(m):
    sendMail(m, getData(m))

if __name__ == "__main__":
    init()
    for m in getMembers():
        T(m).start()
