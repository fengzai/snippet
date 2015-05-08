/*
 *  cloud@txthinking.com
 */
#include "stdio.h"
#include "string.h"

#include "npapi.h"
#include "npruntime.h"
#include "npfunctions.h"

#define MIME_TYPES_DESCRIPTION "application/x-fuck-all:x-fuck-all:just fuck all"

typedef int16_t int16;
typedef uint16_t uint16;
typedef uint32_t uint32;

/*
 * 用于保存浏览器提供的 function table
 */
NPNetscapeFuncs* nfs = NULL;
NPObject *npo = NULL;

void logger(const char *msg) {
	fputs(msg, stderr);
}

/**
 * NPClass function table
 */
bool NPN_HasMethod(NPP npp, NPObject *npobj, NPIdentifier methodName){
    logger("NPN_HasMethod\n");
    /* TODO how to compare the name */
    /*NPUTF8* name = nfs->utf8fromidentifier(methodName);*/
    /*if(strcmp(name, "hi")){*/
        /*return true;*/
    /*}*/
    return true;
}

bool NPN_InvokeDefault(NPP npp, NPObject *npobj, const NPVariant *args,
                       uint32_t argCount, NPVariant *result){
    logger("NPN_InvokeDefault\n");
    result->type = NPVariantType_Int32;
    result->value.intValue = 1;
    return true;
}

bool NPN_Invoke(NPP npp, NPObject *npobj, NPIdentifier methodName,
                const NPVariant *args, uint32_t argCount, NPVariant *result){
    logger("NPN_Invoke\n");
    /* TODO how to return a value */
    /*INT32_TO_NPVARIANT(9, result);*/
    return true;
}

static NPClass npc = {
	NP_CLASS_STRUCT_VERSION,
	NULL,
	NULL,
	NULL,
	NPN_HasMethod,
	NPN_Invoke,
	NPN_InvokeDefault,
	NULL,
	NULL,
	NULL,
	NULL,
};

/**
 * plugin要实现的函数
 */
NPError NPP_New(NPMIMEType    pluginType,
                NPP instance, uint16 mode,
                int16 argc,   char *argn[],
                char *argv[], NPSavedData *saved){
    logger("NPP_New\n");
    /*生产一个NPObject存到instance里*/
    if (!npo){
        npo = nfs->createobject(instance, &npc);
    }
    nfs->retainobject(npo);
    instance->pdata = npo;
    return NPERR_NO_ERROR;
}

NPError NP_LOADDS NPP_GetValue(NPP instance, NPPVariable variable, void *value){
    if(variable == NPPVpluginNameString){
        *((char **)value) = "fuck";
        return NPERR_NO_ERROR;
    }
    if(variable == NPPVpluginDescriptionString){
        *((char **)value) = "fuck all the world";
        return NPERR_NO_ERROR;
    }
    if(variable == NPPVpluginScriptableNPObject){
        if (!npo){
            npo = nfs->createobject(instance, &npc);
        }
		nfs->retainobject(npo);
        *(NPObject **)value = npo;
        logger("NPP_GetValue 3\n");
    }
    if(variable == NPPVpluginNeedsXEmbed){
        logger("NPP_GetValue 4\n");
        return NPERR_GENERIC_ERROR;
    }
    logger("NPP_GetValue 0\n");
    return NPERR_NO_ERROR;
}

NPError NP_LOADDS NPP_SetValue(NPP instance, NPNVariable variable, void *value){
    logger("NPP_SetValue\n");
    return NPERR_NO_ERROR;
}

NPError NPP_Destroy(NPP instance, 
                    NPSavedData **save){
    if(npo){
		nfs->releaseobject(npo);
    }
	npo = NULL;
    logger("NPP_Destroy\n");
    return NPERR_NO_ERROR;
}

/**
 * EXPORT 初始化插件
 */
OSCALL NPError NP_Initialize(NPNetscapeFuncs *aNPNFuncs, NPPluginFuncs *aNPPFuncs){
    logger("NP_Initialize\n");
    nfs = aNPNFuncs;

    aNPPFuncs->version = 1;
    aNPPFuncs->newp = NPP_New;
    aNPPFuncs->destroy = NPP_Destroy;
    aNPPFuncs->getvalue = NPP_GetValue;
    aNPPFuncs->setvalue = NPP_SetValue;

    return NPERR_NO_ERROR;
}

/*
 * EXPORT 注册 MIME
 */
OSCALL const char* NP_GetMIMEDescription(void){
    logger("NP_GetMIMEDescription\n");
    return(MIME_TYPES_DESCRIPTION);
}

/**
 * EXPORT 允许浏览器查看plugin信息
 */
OSCALL NPError NP_GetValue(void *instance, 
                    NPPVariable variable, 
                    void *value){
    logger("NP_GetValue\n");
    if(variable == NPPVpluginNameString){
        *((char **)value) = "fuck";
    }
    if(variable == NPPVpluginDescriptionString){
        *((char **)value) = "fuck all the world";
    }
    return NPERR_NO_ERROR;
}

/**
 * EXPORT 停止
 */
OSCALL NPError NP_Shutdown(void){
    logger("NP_Shutdown\n");
    return NPERR_NO_ERROR;
}


