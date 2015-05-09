#/**
#* @file installArch.sh
#* @brief install archlinux system
#* @author cloud@txthinking.com
#* @version 0.0.1
#* @date 2012-06-25
#*/

#First in usb

# wuxian
# ip link set wlan0 up
# wifi-menu wlan0
# vi /etc/resolv.conf
# nameserver 8.8.8.8
# nameserver 8.8.4.4

#youxian
# ip link set eth0 up
# # ip addr add 192.168.1.2/255.255.255.0 dev eth0
# # ip route add default via 192.168.1.1
# # vi /etc/resolv.conf
# nameserver 8.8.8.8
# nameserver 8.8.4.4

#DSL 
# pppoe-setup
# username
# eth0
# no
# 8.8.8.8
# 8.8.4.4
# 1
# password
# y

# cfdisk /dev/sda
# lsblk /dev/sda
# mkfs.ext4 /dev/sda1
# mkswap /dev/sdb5
# swapon /dev/sda5

# mount /dev/sda1 /mnt

#vi /etc/pacman.d/mirrorlist
# pacman -Syy

# pacstrap /mnt base base-devel
# genfstab -p /mnt >> /mnt/etc/fstab
# arch-chroot /mnt

#Second in your system

echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen
echo "zh_CN.UTF-8 UTF-8" >> /etc/locale.gen
locale-gen
echo "LANG=en_US.UTF-8" >> /etc/locale.conf
echo Asia/Shanghai > /etc/timezone
ln -s /usr/share/zoneinfo/Asia/Shanghai /etc/localtime
hwclock --systohc --utc
echo tx-arch > /etc/hostname

pacman --noconfirm -S grub-bios
grub-install --recheck /dev/sda
cp /usr/share/locale/en\@quot/LC_MESSAGES/grub.mo /boot/grub/locale/en.mo
grub-mkconfig -o /boot/grub/grub.cfg

lspci | grep -i net
#pacman -S r8168
#modprobe -r r8169
#modprobe r8168
#echo "options rtl8192ce fwlps=0" >> /etc/modprobe.d/modprobe.conf
#tee /etc/modules-load.d/r8169.conf <<< "r8169"

lspci | grep VGA
pacman --noconfirm -S xf86-video-intel xf86-video-ati
pacman --noconfirm -S xf86-input-synaptics

pacman --noconfirm -S alsa-utils

useradd -m -g users -s /bin/bash tx
groupadd tx
gpasswd -a tx tx
usermod -a -G audio,disk,floppy,games,locate,lp,network,optical,power,scanner,storage,sys,uucp,video,wheel tx

pacman --noconfirm -S xorg-server xorg-xinit xorg-utils xorg-server-utils xorg-twm xorg-xclock xterm
pacman --noconfirm -S xfce4 gstreamer0.10-base-plugins xorg-xconsole
pacman --noconfirm -S ttf-dejavu ttf-arphic-ukai ttf-arphic-uming

pacman  --noconfirm -S wireless_tools wpa_supplicant wpa_actiond ifplugd dialog netctl hostapd
#for network
echo "nameserver 127.0.0.1" > /etc/resolv.conf
echo "nameserver 8.8.8.8" >> /etc/resolv.conf
echo "nohook resolv.conf" >> /etc/dhcpcd.conf
echo "nooption domain_name_servers" >> /etc/dhcpcd.conf

pacman --noconfirm -S sudo bash-completion dnsutils nmap tcpdump miredo dnsmasq openssh samba tmux irssi ctags gvim p7zip unrar unzip git subversion mercurial strace gdb mplayer rtorrent mutt getmail procmail msmtp w3m whois ffmpeg ix curl wget rsync rdesktop aria2 gnu-netcat cmake imagemagick ntp privoxy 

pacman --noconfirm -S chromium fcitx fcitx-gtk2 fcitx-libpinyin
#google-talkplugin chromium-libpdf-stable chromium-pepper-flash-stable

pacman --noconfirm -S virtualbox qt virtualbox-guest-iso
echo "vboxdrv" >> /etc/modules-load.d/virtualbox.conf
echo "vboxnetadp" >> /etc/modules-load.d/virtualbox.conf
echo "vboxnetflt" >> /etc/modules-load.d/virtualbox.conf
usermod -a -G vboxusers tx

echo "[multilib]" >> /etc/pacman.conf
echo "SigLevel = PackageRequired" >> /etc/pacman.conf
echo "Include = /etc/pacman.d/mirrorlist" >> /etc/pacman.conf
pacman -Syy
pacman --noconfirm -S lib32-glibc

cp /etc/skel/.xinitrc /home/tx/.xinitrc
echo 'export GTK_IM_MODULE=fcitx' >> /home/tx/.xinitrc
echo 'export QT_IM_MODULE=fcitx' >> /home/tx/.xinitrc
echo 'export XMODIFIERS="@im=fcitx"' >> /home/tx/.xinitrc
echo 'export EDITOR=vim' >> /home/tx/.xinitrc
echo 'setxkbmap -option caps:escape' >> /home/tx/.xinitrc
echo "exec startxfce4" >> /home/tx/.xinitrc
chown -R tx:users /home/tx/

#Third after intall
echo "rootPassword,txPassword,visudo,exit,umount,reboot"
#passwd
#passwd tx
#visudo -f /etc/sudoers
#%wheel  ALL=(ALL) ALL
#exit
#umount /mnt/
#reboot

# systemctl enable miredo
# systemctl enable sshd
# systemctl enable dnsmasq
# systemctl enable smbd
# systemctl enable ntpd
# systemctl enable cronie

#install aur
#tar -zxvf xxx.tar.gz;cd xxx;makepkg -si;
