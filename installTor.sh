#/**
#* @file torInstall.sh
#* @brief install tor, need sudo
#* @author cloud@txthinking.com
#* @version 0.0.1
#* @date 2013-06-25
#*/

echo "2620::6b0:b:1a1a:0:26e5:4808 git.torproject.org" >> /etc/hosts

mkdir /var/log/tor
mkdir /var/lib/tor

cd /usr/local/
git clone https://git.torproject.org/tor.git
cd tor
./autogen.sh
./configure  --disable-asciidoc
make && make install

cd /usr/local/
git clone https://git.torproject.org/pluggable-transports/obfsproxy.git
cd obfsproxy
pacman -S python-pip setuptools
python setup.py install

chown -R tx:tx /var/log/tor
chown -R tx:tx /var/lib/tor
