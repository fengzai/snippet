/*
 * cloud@txthinking.com
 */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include "ppapi/c/pp_errors.h"
#include "ppapi/c/pp_module.h"
#include "ppapi/c/pp_var.h"
#include "ppapi/c/ppb.h"
#include "ppapi/c/ppb_console.h"
#include "ppapi/c/ppb_instance.h"
#include "ppapi/c/ppb_messaging.h"
#include "ppapi/c/ppb_var.h"
#include "ppapi/c/ppp.h"
#include "ppapi/c/ppp_instance.h"
#include "ppapi/c/ppp_messaging.h"

#if defined(__native_client__)
#if defined(__pnacl__)
#define TCNAME "pnacl"
#elif defined(__GLIBC__)
#define TCNAME "glibc"
#else
#define TCNAME "newlib"
#endif
#else
#define TCNAME "host"
#endif

#define MIN(a, b) (((a) < (b)) ? (a) : (b))

static PPB_Console* ppb_console_interface = NULL;
static PPB_Messaging* ppb_messaging_interface = NULL;
static PPB_Var* ppb_var_interface = NULL;

/*******************************************************************

*******************************************************************/

/**
 * 从 char* 创建 PP_Var 变量
 */
static struct PP_Var CharPointToPPVar(const char* str) {
    if (ppb_var_interface != NULL) {
        return ppb_var_interface->VarFromUtf8(str, strlen(str));
    }
    return PP_MakeUndefined();
}

/**
 * 从 PP_Var 转换到 char*
 */
uint32_t  VarToCharPoint(struct PP_Var var, char* buffer, uint32_t length) {
    if (ppb_var_interface != NULL) {
        uint32_t var_length;
        const char* str = ppb_var_interface->VarToUtf8(var, &var_length);
        /* str is NOT NULL-terminated. Copy using memcpy. */
        uint32_t min_length = MIN(var_length, length - 1);
        memcpy(buffer, str, min_length);
        buffer[min_length] = 0;

        return min_length;
    }
    return 0;
}

/**
 * 发送消息给 javascript
 */
static void SendMessage(PP_Instance instance, const char* str) {
    if (ppb_messaging_interface) {
        struct PP_Var var = CharPointToPPVar(str);
        ppb_messaging_interface->PostMessage(instance, var);
        ppb_var_interface->Release(var);
    }
}

/**
 * 向 console 发送消息
 */
static void SendLog(PP_Instance instance, const char* str) {
    if (ppb_console_interface) {
        struct PP_Var var = CharPointToPPVar(str);
        ppb_console_interface->Log(instance, PP_LOGLEVEL_ERROR, var);
        ppb_var_interface->Release(var);
    }
}

/*******************************************************************
                    PPP_Instance interface 结构成员
*******************************************************************/
/**
 * PPP_Instance 成员, instance 创建时调用
 */
static PP_Bool DidCreate(PP_Instance instance,
        uint32_t argc,
        const char* argn[],
        const char* argv[]) {

    const char* console_msg = "instance 成功创建(" TCNAME ")!";
    SendLog(instance, console_msg);

    return PP_TRUE;
}

/**
 * PPP_Instance 成员, instance 销毁时调用
 */
static void DidDestroy(PP_Instance instance) {
}

/**
 * PPP_Instance 成员, instance 大小位置等属性改变时调用
 */
static void DidChangeView(PP_Instance instance,
        PP_Resource view_resource) {
}

/**
 * PPP_Instance 成员, instance 焦点改变时调用
 */
static void DidChangeFocus(PP_Instance instance, PP_Bool has_focus) {
}

/**
 * PPP_Instance 成员, 文档加载完时调用
 */
static PP_Bool HandleDocumentLoad(PP_Instance instance,
        PP_Resource url_loader) {
    /* NaCl modules do not need to handle the document load function. */
    return PP_FALSE;
}

/*******************************************************************
                    PPP_Messaging interface 结构成员
*******************************************************************/
/**
 * 接收 js 发来的消息
 */
static void HandleMessage(PP_Instance instance, struct PP_Var message){
    char buffer[1024];
    VarToCharPoint(message, &buffer[0], 1024);

    const char* you_said = " From You.";
    char* response_message = strcat(&buffer[0], you_said);
    SendMessage(instance, response_message);
    if (ppb_var_interface != NULL) {
        ppb_var_interface->Release(message);
    }
}

/*******************************************************************
                        一些硬伤级别函数
*******************************************************************/
/**
 * module 加载初始化调用
 */
PP_EXPORT int32_t PPP_InitializeModule(PP_Module a_module_id,
        PPB_GetInterface get_browser_interface) {
    ppb_console_interface = (PPB_Console*)(get_browser_interface(PPB_CONSOLE_INTERFACE));
    ppb_messaging_interface = (PPB_Messaging*)(get_browser_interface(PPB_MESSAGING_INTERFACE));
    ppb_var_interface = (PPB_Var*)(get_browser_interface(PPB_VAR_INTERFACE));

    /*ppp_messaging_interface = (PPP_Messaging*)(get_browser_interface(PPP_MESSAGING_INTERFACE));*/
    return PP_OK;
}

/**
 * 浏览器调用 module 的 interface 时调用 not recommended
 * PPP_Instance 必须实现, 其他可选o
 * 我靠, 可选的那些返回的 NULL  怎么用 TODO
 */
PP_EXPORT const void* PPP_GetInterface(const char* interface_name) {
    if (strcmp(interface_name, PPP_INSTANCE_INTERFACE) == 0) {
        static PPP_Instance ppp_instance_interface = {
            &DidCreate,
            &DidDestroy,
            &DidChangeView,
            &DidChangeFocus,
            &HandleDocumentLoad,
        };
        return &ppp_instance_interface;
    }
    if (strcmp(interface_name, PPP_MESSAGING_INTERFACE) == 0) {
        static PPP_Messaging ppp_messaging_interface = {
            &HandleMessage,
        };
        return &ppp_messaging_interface;
    }
    return NULL;
}

/**
 * 在 module  unload 之前调用
 */
PP_EXPORT void PPP_ShutdownModule() {
}
