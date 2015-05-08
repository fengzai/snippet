/*
* just test objectc on linux
*/

#import <Foundation/Foundation.h>

//interface
@interface Tx: NSObject{
    int i;
}
-(int) print: (int) n;
@end

//implementation
@implementation Tx
-(int) print: (int) n {
    NSLog (@"you inpurt is %i.", n);
    i = 1;
    int r = n + i;
    return r;
}
@end

int main (int argc, const char * argv[])
{
        NSAutoreleasePool * pool = [[NSAutoreleasePool alloc] init];

        //Tx * tx;
        //tx = [Tx alloc];
        //tx = [tx init];
        Tx * tx = [Tx new];

        int r = [tx print: 2];
        [tx release];

        NSLog (@"result is %i", r);
        [pool drain];
        return 0;
}
