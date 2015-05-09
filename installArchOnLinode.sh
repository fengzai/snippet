#
# lang
#
echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen
echo "zh_CN.UTF-8 UTF-8" >> /etc/locale.gen
locale-gen
echo "LANG=en_US.UTF-8" >> /etc/locale.conf

#
# set hostname
#
hostnamectl set-hostname tx-arch
echo -n `curl ifconfig.cc` >> /etc/hosts
echo "tx-arch" >> /etc/hosts

#
# set timezone
#
timedatectl set-timezone Asia/Hong_Kong

#
# pacman key
#
haveged -w 1024
pacman-key --init
pkill haveged
pacman-key --populate archlinux

#
# update system
#
pacman --noconfirm -Syu

# 
# soft
#
pacman --noconfirm -S base base-devel
pacman --noconfirm -S sudo bash-completion dnsutils nmap tcpdump dnsmasq openssh ctags p7zip unzip git subversion mercurial strace gdb w3m whois ix curl wget rsync gnu-netcat cmake ntp netctl iproute2

#
# add user tx
#
useradd -m -g users -s /bin/bash tx
usermod -a -G wheel tx

#
# for dns
#
echo "nameserver 127.0.0.1" > /etc/resolv.conf
echo "nameserver 8.8.8.8" >> /etc/resolv.conf
echo "nohook resolv.conf" >> /etc/dhcpcd.conf
echo "nooption domain_name_servers" >> /etc/dhcpcd.conf

# 
# ssh disable password login, only allow ssh key is best TODO
#
#echo "PasswordAuthentication no" >> /etc/ssh/sshd_config

#
# ssh disable root login
#
echo "PermitRootLogin no" >> /etc/ssh/sshd_config

#
# about fail2ban, but follow link. so ssh key is better
# https://wiki.archlinux.org/index.php/fail2ban
#

#
# set firewall TODO
#

#
# static ip
#
echo "Description='A basic static ethernet connection'" >> /etc/netctl/static
echo "Interface=eth0" >> /etc/netctl/static
echo "Connection=ethernet" >> /etc/netctl/static

echo "## IPv4 Static Configuration" >> /etc/netctl/static
echo "IP=static" >> /etc/netctl/static
echo "Address=('23.239.0.7/255.255.255.0')" >> /etc/netctl/static
echo "Gateway='23.239.0.1'" >> /etc/netctl/static

echo "## For IPv6 autoconfiguration" >> /etc/netctl/static
echo "#IP6=stateless" >> /etc/netctl/static
echo "## For IPv6 static addr" >> /etc/netctl/staticess configuration
echo "IP6=static" >> /etc/netctl/static
echo "Address6=('2600:3c01::f03c:91ff:fedb:a43b/64')" >> /etc/netctl/static
echo "Gateway6='fe80::1'" >> /etc/netctl/static
echo "## DNS resolvers" >> /etc/netctl/static
echo "DNS=('127.0.0.1' '8.8.8.8')" >> /etc/netctl/static

#
# end todo
#
echo "change tx's password"
echo "visudo allow wheel group"
echo "check interface, ip, dns in /etc/netctl/static"
echo "enable dnsmasq"

#systemctl start dnsmasq
#systemctl enable dnsmasq
#systemctl disable dhcpcd@eth0
#netctl enable static
#systemctl restart sshd
