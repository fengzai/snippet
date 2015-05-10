#!/bin/bash
# on ubuntu
# $ curl -s https://raw.githubusercontent.com/txthinking/snippet/master/installShadowsocks.sh | sh

# first of all
apt-get -y install git golang
echo 'export GOPATH=~/go' >> ~/.bashrc
echo 'export PATH=$PATH:$GOPATH/bin' >> ~/.bashrc
#source ~/.bashrc
export GOPATH=~/go
export PATH="$PATH:$GOPATH/bin"
GOPATH=~/go go get github.com/shadowsocks/shadowsocks-go/cmd/shadowsocks-server
echo '* soft nofile 51200' >> /etc/security/limits.conf
echo '* hard nofile 51200' >> /etc/security/limits.conf

# run
ulimit -n 51200
sysctl -w fs.file-max=51200
sysctl -w net.core.rmem_max=67108864
sysctl -w net.core.wmem_max=67108864
sysctl -w net.core.netdev_max_backlog=250000
sysctl -w net.core.somaxconn=4096

sysctl -w net.ipv4.tcp_syncookies=1
sysctl -w net.ipv4.tcp_tw_reuse=1
sysctl -w net.ipv4.tcp_tw_recycle=0
sysctl -w net.ipv4.tcp_fin_timeout=30
sysctl -w net.ipv4.tcp_keepalive_time=1200
sysctl -w net.ipv4.ip_local_port_range="10000 65000"
sysctl -w net.ipv4.tcp_max_syn_backlog=8192
sysctl -w net.ipv4.tcp_max_tw_buckets=5000
sysctl -w net.ipv4.tcp_fastopen=3
sysctl -w net.ipv4.tcp_mem="25600 51200 102400"
sysctl -w net.ipv4.tcp_rmem="4096 87380 67108864"
sysctl -w net.ipv4.tcp_wmem="4096 65536 67108864"
sysctl -w net.ipv4.tcp_mtu_probing=1
sysctl -w net.ipv4.tcp_congestion_control=hybla

echo '> $ source ~/.bashrc'
echo '> $ shadowsocks-server -h'
echo '> $ killall shadowsocks-server && nohup shadowsocks-server -k "PASSWORD" -p 9000 &'
