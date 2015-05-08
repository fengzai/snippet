/**
 * 同事侄子的作业, 数组相邻元素a,b,c
 * 如果a,b差绝对值不大于b,c绝对值则移除a,b, 并从头再次算起
 * 如果a,b未被移除, 则计算b,c,d
 * TODO 还可以再优化, 如果删除两个元素后重头再来时, 可以标记位置减少比较次数
 *
 * cloud@txthinking.com
 */
#include <stdio.h>
#include <stdlib.h>
#include <math.h>
 
int main(){
    int a[10] = {5, 7, 10, 8, 1, 8, 3, 6, 1, 8};
    int al, l;
    int *b, *c;
    int i,j,k;
    al = l = sizeof(a)/sizeof(int);
    b = (int *)calloc(l, sizeof(int));
    c = (int *)calloc(l, sizeof(int));
    for(i=0; i<l; i++){
        b[i] = a[i];
        c[i] = a[i];
    }

    for(i=0;i<al/2;i++){
        // 打印筛选结果
        int ii = 0;
        for(ii=0;ii<l;ii++){
            printf("%d ", b[ii]);
        }
        printf("\n");

        if(l < 3){
            break;
        }
        j = k = 0;
        for(;;){
            if(abs(b[j]-b[j+1]) <= abs(b[j+1]-b[j+2])){
                printf("\t元素:%d\t减少\n\n", b[j]);
                for(j=j+2;j<l;j++){
                    c[k] = b[j];
                    k++;
                }
                b = c;
                l -= 2;
                break;
            }else{
                printf("\t元素:%d\t没减\n\n", b[j]);
                c[k] = b[j];
                k++;
                j++;
                if(j == l-2){
                    c[k] = b[j];
                    k++;
                    c[k] = b[j+1];
                    b = c;
                    break;
                }
            }
        }
    }
    return 0;
}
