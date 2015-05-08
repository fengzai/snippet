ssh -qTfnN -D 7070 tx@blog.txthinking.com -p 7777
#参数解释:
#-q : be very quite, we are acting only as a tunnel.
#-T : Do not allocate a pseudo tty, we are only acting a tunnel.
#-f : move the ssh process to background, as we don’t want to interact with this ssh session directly.
#-N : Do not execute remote command.
#-n : redirect standard input to /dev/null.
#PS: 查看ssh是否打开7070端口
#netstat -anp | more
