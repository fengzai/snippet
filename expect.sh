#!/usr/bin/expect
# test expect
#

# 用 spawn 包裹命令
spawn ssh -l root 192.168.2.206 "/root/taoxu/tmp.sh"
# 询问时的输出 Password:
expect "Password:"
# 发送输入给刚才的询问
send "sohojoyport\r"
# 返回
interact


# file: tmp.sh
# cd /root/taoxu/
# ls
# echo "aaa" > /root/taoxu/tmp

