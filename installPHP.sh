#!/bin/bash

# archlinux
pacman --noconfirm -S libaio libxml2 zlib pcre openssl curl make gcc autoconf

# ubuntu 13.04
apt-get -y install libaio1 libxml2-dev zlib1g-dev libcurl4-openssl-dev g++ autoconf libpcre3-dev

# centos 6.5
yum -y install libaio-devel libxml2-devel zlib-devel libcurl-devel pcre-devel gcc gcc-c++ autoconf make openssl-devel

INSTALL_DIR=/usr/local/
DOWNLOAD_DIR=/usr/local/Downloads/
PACKAGE_SERVER=http://txthinking.u.qiniudn.com/packages/
WEB_SERVER_USER=tx

`command -v curl >/dev/null 2>&1 || exit 1`
if [ $? -ne 0 ]
then
    echo "[ERROR] Require curl"
    exit 0
fi

if [ ! -d $DOWNLOAD_DIR ]
then
    mkdir $DOWNLOAD_DIR
fi

cd $INSTALL_DIR

#
# param string
#
function notice(){
    echo "----------------------"
    echo $1
    echo $1 >> ${INSTALL_DIR}INSTALL_INFO
    echo "----------------------"
}

#
# param package name
#
function down(){
    DOWNLOAD_ERROR_CODE=1
    if [ ! -f "${DOWNLOAD_DIR}$1" ]
    then
        notice "$1 will be downloading"
        notFound=`curl -s -I "${PACKAGE_SERVER}$1" | grep -Ec "404 Not Found"`
        if [ $notFound -ne 0 ]
        then
            echo "[ERROR] Cannot find ${PACKAGE_SERVER}$1"
            exit $DOWNLOAD_ERROR_CODE
        fi
        curl -s -o "${DOWNLOAD_DIR}$1" "${PACKAGE_SERVER}$1"
        if [ $? -ne 0 ]
        then
            echo "[ERROR] Cannot request ${PACKAGE_SERVER}$1"
            exit $DOWNLOAD_ERROR_CODE
        fi
    else
        notice "$1 aleady exist"
    fi
}

#
# param package name
# 
function check(){
    INSTALL_ERROR_CODE=2
    if [ $? -ne 0 ]
    then
        notice "[ERROR] Cannot install $1"
        exit $INSTALL_ERROR_CODE
    fi
}

#
# mysql-5.5.28-linux2.6-x86_64.tar.gz
#
cd $INSTALL_DIR
NAME=mysql-5.5.28-linux2.6-x86_64.tar.gz
down $NAME
groupadd mysql
useradd -r -g mysql mysql
cd /usr/local
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
mv mysql-5.5.28-linux2.6-x86_64 mysql
cd mysql
chown -R mysql .
chgrp -R mysql .
chown -R root .
chown -R mysql data
mkdir /var/log/mysql
touch /var/log/mysql/error.log
chown -R root /var/log/mysql
chown -R mysql /var/log/mysql
chgrp -R mysql /var/log/mysql
mkdir /var/lib/mysql
chown -R root /var/lib/mysql
chown -R mysql /var/lib/mysql
chgrp -R mysql /var/lib/mysql
cp /usr/local/mysql/share/english/errmsg.sys /usr/local/share/errmsg.sys
./scripts/mysql_install_db --user=mysql --basedir=/usr/local/mysql
#cp support-files/my-medium.cnf /etc/my.cnf
#./bin/mysqld_safe --user=mysql --basedir=/usr/local/mysql &
#./bin/mysqladmin -u root password 111111

#
# libxml2-2.9.1.tar.gz
#
#NAME=libxml2-2.9.1.tar.gz
#down $NAME
#cd $INSTALL_DIR
#tar -zxvf "${DOWNLOAD_DIR}${NAME}"
#cd libxml2-2.9.1
#./configure --prefix=${INSTALL_DIR}libxml2
#make
#make install
#cd $INSTALL_DIR
#rm -rf libxml2-2.9.1
#check $NAME

#
# zlib-1.2.8.tar.gz
#
#NAME=zlib-1.2.8.tar.gz
#down $NAME
#cd $INSTALL_DIR
#tar -zxvf "${DOWNLOAD_DIR}${NAME}"
#cd zlib-1.2.8
#./configure --prefix=${INSTALL_DIR}zlib
#make
#make install
#cd $INSTALL_DIR
#rm -rf zlib-1.2.8
#check $NAME

#
# openssl
# use system builtin
#

#
# curl
# use system builtin
#

#
# libpng-1.5.9.tar.gz
#
NAME=libpng-1.5.9.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd libpng-1.5.9
./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf libpng-1.5.9

#
# jpegsrc.v9.tar.gz
#
NAME=jpegsrc.v9.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd jpeg-9
./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf jpeg-9

#
# freetype-2.4.8.tar.gz
#
NAME=freetype-2.4.8.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd freetype-2.4.8
./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf freetype-2.4.8

#
# libgd-2.1.0.tar.gz
#
NAME=libgd-2.1.0.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd libgd-2.1.0
./configure --with-jpeg=/usr/local/ \
    --with-png=/usr/local/ \
    --with-freetype=/usr/local/ 
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf libgd-2.1.0

#
# libmcrypt-2.5.8.tar.gz
#
NAME=libmcrypt-2.5.8.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd libmcrypt-2.5.8
./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf libmcrypt-2.5.8

#
# mhash-0.9.9.9.tar.gz
#
NAME=mhash-0.9.9.9.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd mhash-0.9.9.9
./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf mhash-0.9.9.9

#
# mcrypt-2.6.8.tar.gz
#
NAME=mcrypt-2.6.8.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd mcrypt-2.6.8
echo 'export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/lib' >> /etc/profile
ln -s /usr/local/bin/libmcrypt_config /usr/bin/libmcrypt_config
LD_LIBRARY_PATH=/usr/local/lib ./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf mcrypt-2.6.8

#
# pcre-8.30.tar.gz
#
#NAME=pcre-8.30.tar.gz
#down $NAME
#cd $INSTALL_DIR
#tar -zxvf "${DOWNLOAD_DIR}${NAME}"
#cd pcre-8.30
#./configure
#make
#make install
#cd $INSTALL_DIR
#rm -rf pcre-8.30
#check $NAME

#
# php-5.5.4.tar.gz
#
NAME=php-5.5.4.tar.gz 
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd php-5.5.4
./configure \
    --enable-fpm \
    --enable-calendar \
    --enable-exif \
    --enable-mbstring \
    --enable-sockets \
    --enable-soap \
    --enable-zip \
    --with-fpm-user=${WEB_SERVER_USER} \
    --with-fpm-group=${WEB_SERVER_USER} \
    --with-libxml-dir \
    --with-pcre-regex \
    --with-zlib \
    --with-zlib-dir \
    --with-pcre-dir \
    --with-gd \
    --with-jpeg-dir \
    --with-png-dir \
    --with-freetype-dir \
    --with-mhash \
    --with-mcrypt \
    --with-mysql=${INSTALL_DIR}mysql \
    --with-mysqli=${INSTALL_DIR}mysql/bin/mysql_config \
    --with-pdo-mysql=${INSTALL_DIR}mysql \
    --with-openssl \
    --with-openssl-dir \
    --with-curl
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf php-5.5.4

#
# libevent-2.0.17-stable.tar.gz
#
NAME=libevent-2.0.17-stable.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd libevent-2.0.17-stable
./configure 
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf libevent-2.0.17-stable

#
# memcached-1.4.13.tar.gz
#
NAME=memcached-1.4.13.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd memcached-1.4.13
./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf memcached-1.4.13
check $NAME

#
# libmemcached-1.0.4.tar.gz
#
NAME=libmemcached-1.0.4.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd libmemcached-1.0.4
./configure
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf libmemcached-1.0.4

#
# memcached-2.0.1.tgz
#
NAME=memcached-2.0.1.tgz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd memcached-2.0.1
/usr/local/bin/phpize
check $NAME
./configure --with-php-config=/usr/local/bin/php-config
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf memcached-2.0.1

#
# memcache-2.2.7.tgz
#
NAME=memcache-2.2.7.tgz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd memcache-2.2.7
/usr/local/bin/phpize
check $NAME
./configure --with-php-config=/usr/local/bin/php-config
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf memcache-2.2.7

# xdebug-2.2.3.tgz
NAME=xdebug-2.2.3.tgz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd xdebug-2.2.3
/usr/local/bin/phpize
check $NAME
./configure --with-php-config=/usr/local/bin/php-config
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf xdebug-2.2.3

#
# mongo-1.4.4.tgz
#
NAME=mongo-1.4.4.tgz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd mongo-1.4.4
/usr/local/bin/phpize
check $NAME
./configure --with-php-config=/usr/local/bin/php-config
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf mongo-1.4.4

#
# redis-2.2.5.tgz
#
NAME=redis-2.2.5.tgz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd redis-2.2.5
/usr/local/bin/phpize
check $NAME
./configure --with-php-config=/usr/local/bin/php-config
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf redis-2.2.5

#
# nginx-1.4.1.tar.gz
#
cd $INSTALL_DIR
NAME=nginx-1.4.1.tar.gz
down $NAME
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd nginx-1.4.1
./configure \
    --user=$WEB_SERVER_USER \
    --group=$WEB_SERVER_USER \
    --with-rtsig_module \
    --with-select_module \
    --with-poll_module \
    --with-file-aio \
    --with-ipv6 \
    --with-http_ssl_module \
    --with-http_realip_module \
    --with-http_addition_module \
    --with-http_sub_module \
    --with-http_dav_module \
    --with-http_flv_module \
    --with-http_mp4_module \
    --with-http_gzip_static_module \
    --with-http_random_index_module \
    --with-http_secure_link_module \
    --with-http_degradation_module \
    --with-http_stub_status_module \
    --with-debug \
    --with-pcre
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf nginx-1.4.1

#
# httpd-2.2.22.tar.gz
#
NAME=httpd-2.2.22.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd httpd-2.2.22
./configure \
    --enable-v4-mapped \
    --enable-exception-hook \
    --enable-pie \
    --enable-authn-dbm \
    --enable-authn-anon \
    --enable-authn-dbd \
    --enable-authn-alias \
    --enable-authz-dbm \
    --enable-authz-owner \
    --enable-auth-digest \
    --enable-isapi \
    --enable-file-cache \
    --enable-cache \
    --enable-disk-cache \
    --enable-mem-cache \
    --enable-dbd \
    --enable-bucketeer \
    --enable-dumpio \
    --enable-echo \
    --enable-example \
    --enable-case-filter \
    --enable-case-filter-in \
    --enable-reqtimeout \
    --enable-ext-filter \
    --enable-substitute \
    --enable-charset-lite \
    --enable-deflate \
    --enable-log-forensic \
    --enable-logio \
    --enable-mime-magic \
    --enable-cern-meta \
    --enable-expires \
    --enable-headers \
    --enable-ident \
    --enable-usertrack \
    --enable-unique-id \
    --enable-proxy \
    --enable-proxy-connect \
    --enable-proxy-ftp \
    --enable-proxy-http \
    --enable-proxy-scgi \
    --enable-proxy-ajp \
    --enable-proxy-balancer \
    --enable-ssl \
    --enable-optional-hook-export \
    --enable-optional-hook-import \
    --enable-optional-fn-import \
    --enable-optional-fn-export \
    --enable-http \
    --enable-dav \
    --enable-info \
    --enable-suexec \
    --enable-cgi \
    --enable-cgid \
    --enable-dav-fs \
    --enable-dav-lock \
    --enable-vhost-alias \
    --enable-imagemap \
    --enable-speling \
    --enable-rewrite \
    --enable-so
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf httpd-2.2.22

#
# mod_fcgid-2.3.6.tar.gz
#
NAME=mod_fcgid-2.3.6.tar.gz
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd mod_fcgid-2.3.6
APXS=${INSTALL_DIR}apache2/bin/apxs ./configure.apxs
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf mod_fcgid-2.3.6

#
# php-5.5.4.tar.gz
#
NAME=php-5.5.4.tar.gz 
down $NAME
cd $INSTALL_DIR
tar -zxvf "${DOWNLOAD_DIR}${NAME}"
cd php-5.5.4
./configure \
    --with-apxs2=${INSTALL_DIR}apache2/bin/apxs \
    --enable-fpm \
    --enable-calendar \
    --enable-exif \
    --enable-mbstring \
    --enable-sockets \
    --enable-soap \
    --enable-zip \
    --with-fpm-user=${WEB_SERVER_USER} \
    --with-fpm-group=${WEB_SERVER_USER} \
    --with-libxml-dir \
    --with-pcre-regex \
    --with-zlib \
    --with-zlib-dir \
    --with-pcre-dir \
    --with-gd \
    --with-jpeg-dir \
    --with-png-dir \
    --with-freetype-dir \
    --with-mhash \
    --with-mcrypt \
    --with-mysql=${INSTALL_DIR}mysql \
    --with-mysqli=${INSTALL_DIR}mysql/bin/mysql_config \
    --with-pdo-mysql=${INSTALL_DIR}mysql \
    --with-openssl \
    --with-openssl-dir \
    --with-curl
check $NAME
make
check $NAME
make install
check $NAME
cd $INSTALL_DIR
rm -rf php-5.5.4


#
# uwsgi-1.9.tar.gz
#
#NAME=uwsgi-1.9.tar.gz
#down $NAME
#cd $INSTALL_DIR
#tar -zxvf "${DOWNLOAD_DIR}${NAME}"
#cd uwsgi-1.9
#make
#check $NAME
#/usr/local/uwsgi-1.9/uwsgi --socket 127.0.0.1:3031 --wsgi-file /home/tx/workspace/php/index.py --processes 4 --threads 2 --stats 127.0.0.1:9191

#
# over
#./bin/mysqladmin -u root password 111111
echo "\nSuccess!\nDon't forget change mysql password!\n"
