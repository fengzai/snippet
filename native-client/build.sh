#!/bin/sh
a="/home/tx/workspace/nacl_sdk/pepper_29/lib/glibc_x86_64/Debug"
/home/tx/workspace/nacl_sdk/pepper_29/toolchain/linux_x86_glibc/bin/x86_64-nacl-gcc -o tmp.o -c tmp.c $a/* -m64 -g -O0 -pthread -Wno-long-long -Wall -fPIC -I/home/tx/workspace/nacl_sdk/pepper_29/include

/home/tx/workspace/nacl_sdk/pepper_29/toolchain/linux_x86_glibc/bin/x86_64-nacl-gcc -o libtmp.so tmp.o -m64 -g -shared 

