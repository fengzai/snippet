#!/usr/bin/env bash


SDK_INCLUDE=/home/tx/workspace/xulrunner-sdk/include

## compile
#gcc -I${DK_INCLUDE} -c tmp.c -o tmp.o
## build static lib
#ar -r libtmp.a tmp.o
## build shared lib
#gcc -shared tmp.o -o libtmp.so

# build share lib
gcc -I${DK_INCLUDE} -shared -fPIC -m64 -o libtmp.so tmp.c
cp libtmp.so ~/.mozilla/plugins/
