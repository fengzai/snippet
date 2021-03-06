#/**
#* @file installJava.sh
#* @brief  need jdk install file already in /usr/local/
#* @author cloud@txthinking.com
#* @version 0.0.1
#* @date 2013-06-27
#*/

#. funcs/func_down.sh
INSTALL_DIR=/usr/local/
DOWNLOAD_DIR=/usr/local/Downloads/
PACKAGE_SERVER=http://txthinking.u.qiniudn.com/packages/

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

#
# param string
#
function notice(){
    echo "----------------------"
    echo "----------------------" >> ${INSTALL_DIR}INSTALL_INFO
    echo $1
    echo $1 >> ${INSTALL_DIR}INSTALL_INFO
    echo "----------------------"
    echo "----------------------" >> ${INSTALL_DIR}INSTALL_INFO
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

NAME=jdk-6u37-linux-x64.bin
down $NAME

cd $INSTALL_DIR
sh ${DOWNLOAD_DIR}${NAME}
JAVA_HOME=/usr/local/jdk1.6.0_37
echo "JAVA_HOME=$JAVA_HOME" >> /etc/profile
echo "CLASSPATH=.:\$JAVA_HOME/lib" >> /etc/profile
echo "PATH=\$JAVA_HOME/bin:\$PATH" >> /etc/profile 
echo "export JAVA_HOME CLASSPATH PATH" >> /etc/profile
