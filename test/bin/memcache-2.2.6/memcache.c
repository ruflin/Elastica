/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2007 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Antony Dovgal <tony@daylessday.org>                         |
  |          Mikael Johansson <mikael AT synd DOT info>                  |
  +----------------------------------------------------------------------+
*/

/* $Id: memcache.c 303962 2010-10-03 15:48:23Z hradtke $ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include <stdio.h>
#include <fcntl.h>
#ifdef HAVE_SYS_FILE_H
#include <sys/file.h>
#endif

#include <zlib.h>
#include <time.h>
#include "ext/standard/crc32.h"
#include "ext/standard/info.h"
#include "ext/standard/php_string.h"
#include "ext/standard/php_var.h"
#include "ext/standard/php_smart_str.h"
#include "php_network.h"
#include "php_memcache.h"
#include "memcache_queue.h"

#if HAVE_MEMCACHE_SESSION
#include "ext/session/php_session.h"
#endif

#ifndef ZEND_ENGINE_2
#define OnUpdateLong OnUpdateInt
#endif

/* True global resources - no need for thread safety here */
static int le_memcache_pool, le_pmemcache;
static zend_class_entry *memcache_class_entry_ptr;

ZEND_DECLARE_MODULE_GLOBALS(memcache)

/* {{{ memcache_functions[]
 */
zend_function_entry memcache_functions[] = {
	PHP_FE(memcache_connect,		NULL)
	PHP_FE(memcache_pconnect,		NULL)
	PHP_FE(memcache_add_server,		NULL)
	PHP_FE(memcache_set_server_params,		NULL)
	PHP_FE(memcache_get_server_status,		NULL)
	PHP_FE(memcache_get_version,	NULL)
	PHP_FE(memcache_add,			NULL)
	PHP_FE(memcache_set,			NULL)
	PHP_FE(memcache_replace,		NULL)
	PHP_FE(memcache_get,			NULL)
	PHP_FE(memcache_delete,			NULL)
	PHP_FE(memcache_debug,			NULL)
	PHP_FE(memcache_get_stats,		NULL)
	PHP_FE(memcache_get_extended_stats,		NULL)
	PHP_FE(memcache_set_compress_threshold,	NULL)
	PHP_FE(memcache_increment,		NULL)
	PHP_FE(memcache_decrement,		NULL)
	PHP_FE(memcache_close,			NULL)
	PHP_FE(memcache_flush,			NULL)
	PHP_FE(memcache_setoptimeout,	NULL)
	{NULL, NULL, NULL}
};

static zend_function_entry php_memcache_class_functions[] = {
	PHP_FALIAS(connect,			memcache_connect,			NULL)
	PHP_FALIAS(pconnect,		memcache_pconnect,			NULL)
	PHP_FALIAS(addserver,		memcache_add_server,		NULL)
	PHP_FALIAS(setserverparams,		memcache_set_server_params,		NULL)
	PHP_FALIAS(getserverstatus,		memcache_get_server_status,		NULL)
	PHP_FALIAS(getversion,		memcache_get_version,		NULL)
	PHP_FALIAS(add,				memcache_add,				NULL)
	PHP_FALIAS(set,				memcache_set,				NULL)
	PHP_FALIAS(replace,			memcache_replace,			NULL)
	PHP_FALIAS(get,				memcache_get,				NULL)
	PHP_FALIAS(delete,			memcache_delete,			NULL)
	PHP_FALIAS(getstats,		memcache_get_stats,			NULL)
	PHP_FALIAS(getextendedstats,		memcache_get_extended_stats,		NULL)
	PHP_FALIAS(setcompressthreshold,	memcache_set_compress_threshold,	NULL)
	PHP_FALIAS(increment,		memcache_increment,			NULL)
	PHP_FALIAS(decrement,		memcache_decrement,			NULL)
	PHP_FALIAS(close,			memcache_close,				NULL)
	PHP_FALIAS(flush,			memcache_flush,				NULL)
	PHP_FALIAS(setoptimeout,	memcache_setoptimeout,		NULL)
	{NULL, NULL, NULL}
};

/* }}} */

/* {{{ memcache_module_entry
 */
zend_module_entry memcache_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	"memcache",
	memcache_functions,
	PHP_MINIT(memcache),
	PHP_MSHUTDOWN(memcache),
	PHP_RINIT(memcache),
	NULL,
	PHP_MINFO(memcache),
#if ZEND_MODULE_API_NO >= 20010901
	PHP_MEMCACHE_VERSION,
#endif
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_MEMCACHE
ZEND_GET_MODULE(memcache)
#endif

static PHP_INI_MH(OnUpdateChunkSize) /* {{{ */
{
	long int lval;

	lval = strtol(new_value, NULL, 10);
	if (lval <= 0) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "memcache.chunk_size must be a positive integer ('%s' given)", new_value);
		return FAILURE;
	}

	return OnUpdateLong(entry, new_value, new_value_length, mh_arg1, mh_arg2, mh_arg3, stage TSRMLS_CC);
}
/* }}} */

static PHP_INI_MH(OnUpdateFailoverAttempts) /* {{{ */
{
	long int lval;

	lval = strtol(new_value, NULL, 10);
	if (lval <= 0) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "memcache.max_failover_attempts must be a positive integer ('%s' given)", new_value);
		return FAILURE;
	}

	return OnUpdateLong(entry, new_value, new_value_length, mh_arg1, mh_arg2, mh_arg3, stage TSRMLS_CC);
}
/* }}} */

static PHP_INI_MH(OnUpdateHashStrategy) /* {{{ */
{
	if (!strcasecmp(new_value, "standard")) {
		MEMCACHE_G(hash_strategy) = MMC_STANDARD_HASH;
	}
	else if (!strcasecmp(new_value, "consistent")) {
		MEMCACHE_G(hash_strategy) = MMC_CONSISTENT_HASH;
	}
	else {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "memcache.hash_strategy must be in set {standard, consistent} ('%s' given)", new_value);
		return FAILURE;
	}

	return SUCCESS;
}
/* }}} */

static PHP_INI_MH(OnUpdateHashFunction) /* {{{ */
{
	if (!strcasecmp(new_value, "crc32")) {
		MEMCACHE_G(hash_function) = MMC_HASH_CRC32;
	}
	else if (!strcasecmp(new_value, "fnv")) {
		MEMCACHE_G(hash_function) = MMC_HASH_FNV1A;
	}
	else {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "memcache.hash_function must be in set {crc32, fnv} ('%s' given)", new_value);
		return FAILURE;
	}

	return SUCCESS;
}
/* }}} */

static PHP_INI_MH(OnUpdateDefaultTimeout) /* {{{ */
{
	long int lval;

	lval = strtol(new_value, NULL, 10);
	if (lval <= 0) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "memcache.default_timeout must be a positive number greater than or equal to 1 ('%s' given)", new_value);
		return FAILURE;
	}
	MEMCACHE_G(default_timeout_ms) = lval;
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_INI */
PHP_INI_BEGIN()
	STD_PHP_INI_ENTRY("memcache.allow_failover",	"1",		PHP_INI_ALL, OnUpdateLong,		allow_failover,	zend_memcache_globals,	memcache_globals)
	STD_PHP_INI_ENTRY("memcache.max_failover_attempts",	"20",	PHP_INI_ALL, OnUpdateFailoverAttempts,		max_failover_attempts,	zend_memcache_globals,	memcache_globals)
	STD_PHP_INI_ENTRY("memcache.default_port",		"11211",	PHP_INI_ALL, OnUpdateLong,		default_port,	zend_memcache_globals,	memcache_globals)
	STD_PHP_INI_ENTRY("memcache.chunk_size",		"8192",		PHP_INI_ALL, OnUpdateChunkSize,	chunk_size,		zend_memcache_globals,	memcache_globals)
	STD_PHP_INI_ENTRY("memcache.hash_strategy",		"standard",	PHP_INI_ALL, OnUpdateHashStrategy,	hash_strategy,	zend_memcache_globals,	memcache_globals)
	STD_PHP_INI_ENTRY("memcache.hash_function",		"crc32",	PHP_INI_ALL, OnUpdateHashFunction,	hash_function,	zend_memcache_globals,	memcache_globals)
	STD_PHP_INI_ENTRY("memcache.default_timeout_ms",	"1000",	PHP_INI_ALL, OnUpdateDefaultTimeout,	default_timeout_ms,	zend_memcache_globals,	memcache_globals)
PHP_INI_END()
/* }}} */

/* {{{ internal function protos */
static void _mmc_pool_list_dtor(zend_rsrc_list_entry * TSRMLS_DC);
static void _mmc_pserver_list_dtor(zend_rsrc_list_entry * TSRMLS_DC);

static void mmc_server_free(mmc_t * TSRMLS_DC);
static void mmc_server_disconnect(mmc_t * TSRMLS_DC);
static int mmc_server_store(mmc_t *, const char *, int TSRMLS_DC);

static int mmc_compress(char **, unsigned long *, const char *, int TSRMLS_DC);
static int mmc_uncompress(char **, unsigned long *, const char *, int);
static int mmc_get_pool(zval *, mmc_pool_t ** TSRMLS_DC);
static int mmc_readline(mmc_t * TSRMLS_DC);
static char * mmc_get_version(mmc_t * TSRMLS_DC);
static int mmc_str_left(char *, char *, int, int);
static int mmc_sendcmd(mmc_t *, const char *, int TSRMLS_DC);
static int mmc_parse_response(mmc_t *mmc, char *, int, char **, int *, int *, int *);
static int mmc_exec_retrieval_cmd_multi(mmc_pool_t *, zval *, zval **, zval * TSRMLS_DC);
static int mmc_read_value(mmc_t *, char **, int *, char **, int *, int * TSRMLS_DC);
static int mmc_flush(mmc_t *, int TSRMLS_DC);
static void php_mmc_store(INTERNAL_FUNCTION_PARAMETERS, char *, int);
static int mmc_get_stats(mmc_t *, char *, int, int, zval * TSRMLS_DC);
static int mmc_incr_decr(mmc_t *, int, char *, int, int, long * TSRMLS_DC);
static void php_mmc_incr_decr(INTERNAL_FUNCTION_PARAMETERS, int);
static void php_mmc_connect(INTERNAL_FUNCTION_PARAMETERS, int);
/* }}} */

/* {{{ hash strategies */
extern mmc_hash_t mmc_standard_hash;
extern mmc_hash_t mmc_consistent_hash;
/* }}} */

/* {{{ php_memcache_init_globals()
*/
static void php_memcache_init_globals(zend_memcache_globals *memcache_globals_p TSRMLS_DC)
{
	MEMCACHE_G(debug_mode)		  = 0;
	MEMCACHE_G(num_persistent)	  = 0;
	MEMCACHE_G(compression_level) = Z_DEFAULT_COMPRESSION;
	MEMCACHE_G(hash_strategy)	  = MMC_STANDARD_HASH;
	MEMCACHE_G(hash_function)	  = MMC_HASH_CRC32;
	MEMCACHE_G(default_timeout_ms)= (MMC_DEFAULT_TIMEOUT) * 1000;
}
/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(memcache)
{
	zend_class_entry memcache_class_entry;
	INIT_CLASS_ENTRY(memcache_class_entry, "Memcache", php_memcache_class_functions);
	memcache_class_entry_ptr = zend_register_internal_class(&memcache_class_entry TSRMLS_CC);

	le_memcache_pool = zend_register_list_destructors_ex(_mmc_pool_list_dtor, NULL, "memcache connection", module_number);
	le_pmemcache = zend_register_list_destructors_ex(NULL, _mmc_pserver_list_dtor, "persistent memcache connection", module_number);

#ifdef ZTS
	ts_allocate_id(&memcache_globals_id, sizeof(zend_memcache_globals), (ts_allocate_ctor) php_memcache_init_globals, NULL);
#else
	php_memcache_init_globals(&memcache_globals TSRMLS_CC);
#endif

	REGISTER_LONG_CONSTANT("MEMCACHE_COMPRESSED", MMC_COMPRESSED, CONST_CS | CONST_PERSISTENT);
	REGISTER_INI_ENTRIES();

#if HAVE_MEMCACHE_SESSION
	REGISTER_LONG_CONSTANT("MEMCACHE_HAVE_SESSION", 1, CONST_CS | CONST_PERSISTENT);
	php_session_register_module(ps_memcache_ptr);
#else
	REGISTER_LONG_CONSTANT("MEMCACHE_HAVE_SESSION", 0, CONST_CS | CONST_PERSISTENT);
#endif

	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(memcache)
{
	UNREGISTER_INI_ENTRIES();
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(memcache)
{
	MEMCACHE_G(debug_mode) = 0;
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(memcache)
{
	char buf[MAX_LENGTH_OF_LONG + 1];

	sprintf(buf, "%ld", MEMCACHE_G(num_persistent));

	php_info_print_table_start();
	php_info_print_table_header(2, "memcache support", "enabled");
	php_info_print_table_row(2, "Active persistent connections", buf);
	php_info_print_table_row(2, "Version", PHP_MEMCACHE_VERSION);
	php_info_print_table_row(2, "Revision", "$Revision: 303962 $");
	php_info_print_table_end();

	DISPLAY_INI_ENTRIES();
}
/* }}} */

/* ------------------
   internal functions
   ------------------ */

#if ZEND_DEBUG
void mmc_debug(const char *format, ...) /* {{{ */
{
	TSRMLS_FETCH();

	if (MEMCACHE_G(debug_mode)) {
		char buffer[1024];
		va_list args;

		va_start(args, format);
		vsnprintf(buffer, sizeof(buffer)-1, format, args);
		va_end(args);
		buffer[sizeof(buffer)-1] = '\0';
		php_printf("%s<br />\n", buffer);
	}
}
/* }}} */
#endif

static struct timeval _convert_timeoutms_to_ts(long msecs) /* {{{ */
{
	struct timeval tv;
	int secs = 0;

	secs = msecs / 1000;
	tv.tv_sec = secs;
	tv.tv_usec = ((msecs - (secs * 1000)) * 1000) % 1000000;
	return tv;
}
/* }}} */

static void _mmc_pool_list_dtor(zend_rsrc_list_entry *rsrc TSRMLS_DC) /* {{{ */
{
	mmc_pool_free((mmc_pool_t *)rsrc->ptr TSRMLS_CC);
}
/* }}} */

static void _mmc_pserver_list_dtor(zend_rsrc_list_entry *rsrc TSRMLS_DC) /* {{{ */
{
	mmc_server_free((mmc_t *)rsrc->ptr TSRMLS_CC);
}
/* }}} */

mmc_t *mmc_server_new(char *host, int host_len, unsigned short port, int persistent, int timeout, int retry_interval TSRMLS_DC) /* {{{ */
{
	mmc_t *mmc = pemalloc(sizeof(mmc_t), persistent);
	memset(mmc, 0, sizeof(*mmc));
	
	mmc->host = pemalloc(host_len + 1, persistent);
	memcpy(mmc->host, host, host_len);
	mmc->host[host_len] = '\0';
	
	mmc->port = port;
	mmc->status = MMC_STATUS_DISCONNECTED;

	mmc->persistent = persistent;
	if (persistent) {
		MEMCACHE_G(num_persistent)++;
	}

	mmc->timeout = timeout;
	mmc->retry_interval = retry_interval;

	return mmc;
}
/* }}} */

static void mmc_server_callback_dtor(zval **callback TSRMLS_DC) /* {{{ */
{
	zval **this_obj;
	
	if (!callback || !*callback) return;

	if (Z_TYPE_PP(callback) == IS_ARRAY && 
		zend_hash_index_find(Z_ARRVAL_PP(callback), 0, (void **)&this_obj) == SUCCESS &&
		Z_TYPE_PP(this_obj) == IS_OBJECT) {
		zval_ptr_dtor(this_obj);
	}
	zval_ptr_dtor(callback);
}
/* }}} */

static void mmc_server_callback_ctor(zval **callback TSRMLS_DC) /* {{{ */
{
	zval **this_obj;
	
	if (!callback || !*callback) return;

	if (Z_TYPE_PP(callback) == IS_ARRAY && 
		zend_hash_index_find(Z_ARRVAL_PP(callback), 0, (void **)&this_obj) == SUCCESS &&
		Z_TYPE_PP(this_obj) == IS_OBJECT) {
		zval_add_ref(this_obj);
	}
	zval_add_ref(callback);
}
/* }}} */

static void mmc_server_sleep(mmc_t *mmc TSRMLS_DC) /* 
	prepare server struct for persistent sleep {{{ */
{
	mmc_server_callback_dtor(&mmc->failure_callback TSRMLS_CC);
	mmc->failure_callback = NULL;
	
	if (mmc->error != NULL) {
		pefree(mmc->error, mmc->persistent);
		mmc->error = NULL;
	}
}
/* }}} */

static void mmc_server_free(mmc_t *mmc TSRMLS_DC) /* {{{ */
{
	if (mmc->in_free) {
		php_error_docref(NULL TSRMLS_CC, E_ERROR, "Recursive reference detected, bailing out");
		return;
	}
	mmc->in_free = 1;

	mmc_server_sleep(mmc TSRMLS_CC);

	if (mmc->persistent) {
		free(mmc->host);
		free(mmc);
		MEMCACHE_G(num_persistent)--;
	}
	else {
		if (mmc->stream != NULL) {
			php_stream_close(mmc->stream);
		}
		efree(mmc->host);
		efree(mmc);
	}
}
/* }}} */

static void mmc_server_seterror(mmc_t *mmc, const char *error, int errnum) /* {{{ */
{
	if (error != NULL) {
		if (mmc->error != NULL) {
			pefree(mmc->error, mmc->persistent);
		}
		
		mmc->error = pestrdup(error, mmc->persistent);
		mmc->errnum = errnum;
	}
}
/* }}} */

static void mmc_server_received_error(mmc_t *mmc, int response_len)  /* {{{ */
{
	if (mmc_str_left(mmc->inbuf, "ERROR", response_len, sizeof("ERROR") - 1) ||
		mmc_str_left(mmc->inbuf, "CLIENT_ERROR", response_len, sizeof("CLIENT_ERROR") - 1) ||
		mmc_str_left(mmc->inbuf, "SERVER_ERROR", response_len, sizeof("SERVER_ERROR") - 1)) 
	{
		mmc->inbuf[response_len < MMC_BUF_SIZE - 1 ? response_len : MMC_BUF_SIZE - 1] = '\0';
		mmc_server_seterror(mmc, mmc->inbuf, 0);
	}
	else {
		mmc_server_seterror(mmc, "Received malformed response", 0);
	}
}
/* }}} */

int mmc_server_failure(mmc_t *mmc TSRMLS_DC) /*determines if a request should be retried or is a hard network failure {{{ */
{
	switch (mmc->status) {
		case MMC_STATUS_DISCONNECTED:
			return 0;

		/* attempt reconnect of sockets in unknown state */
		case MMC_STATUS_UNKNOWN:
			mmc->status = MMC_STATUS_DISCONNECTED;
			return 0;
	}

	mmc_server_deactivate(mmc TSRMLS_CC);
	return 1;
}
/* }}} */

static int mmc_server_store(mmc_t *mmc, const char *request, int request_len TSRMLS_DC) /* {{{ */
{
	int response_len;
	php_netstream_data_t *sock = (php_netstream_data_t*)mmc->stream->abstract;

	if (mmc->timeoutms > 1) {
		sock->timeout = _convert_timeoutms_to_ts(mmc->timeoutms);
	}

	if (php_stream_write(mmc->stream, request, request_len) != request_len) {
		mmc_server_seterror(mmc, "Failed sending command and value to stream", 0);
		return -1;
	}

	if ((response_len = mmc_readline(mmc TSRMLS_CC)) < 0) {
		return -1;
	}

	if(mmc_str_left(mmc->inbuf, "STORED", response_len, sizeof("STORED") - 1)) {
		return 1;
	}

	/* return FALSE */
	if(mmc_str_left(mmc->inbuf, "NOT_STORED", response_len, sizeof("NOT_STORED") - 1)) {
		return 0;
	}

	/* return FALSE without failover */
	if (mmc_str_left(mmc->inbuf, "SERVER_ERROR out of memory", response_len, sizeof("SERVER_ERROR out of memory") - 1) ||
		mmc_str_left(mmc->inbuf, "SERVER_ERROR object too large", response_len, sizeof("SERVER_ERROR object too large")-1)) {
		return 0;
	}
	
	mmc_server_received_error(mmc, response_len);
	return -1;
}
/* }}} */

int mmc_prepare_key_ex(const char *key, unsigned int key_len, char *result, unsigned int *result_len TSRMLS_DC)  /* {{{ */
{
	unsigned int i;
	if (key_len == 0) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Key cannot be empty");
		return MMC_REQUEST_FAILURE;
	}

	*result_len = key_len < MMC_KEY_MAX_SIZE ? key_len : MMC_KEY_MAX_SIZE;
	result[*result_len] = '\0';
	
	for (i=0; i<*result_len; i++) {
		result[i] = ((unsigned char)key[i]) > ' ' ? key[i] : '_';
	}
	
	return MMC_OK;
}
/* }}} */

int mmc_prepare_key(zval *key, char *result, unsigned int *result_len TSRMLS_DC)  /* {{{ */
{
	if (Z_TYPE_P(key) == IS_STRING) {
		return mmc_prepare_key_ex(Z_STRVAL_P(key), Z_STRLEN_P(key), result, result_len TSRMLS_CC);
	} else {
		int res;
		zval *keytmp;
		ALLOC_ZVAL(keytmp);

		*keytmp = *key;
		zval_copy_ctor(keytmp);
		convert_to_string(keytmp);

		res = mmc_prepare_key_ex(Z_STRVAL_P(keytmp), Z_STRLEN_P(keytmp), result, result_len TSRMLS_CC);

		zval_dtor(keytmp);
		FREE_ZVAL(keytmp);
		
		return res;
	}
}
/* }}} */

static unsigned int mmc_hash_crc32(const char *key, int key_len) /* CRC32 hash {{{ */
{
	unsigned int crc = ~0;
	int i;

	for (i=0; i<key_len; i++) {
		CRC32(crc, key[i]);
	}

  	return ~crc;
}
/* }}} */

static unsigned int mmc_hash_fnv1a(const char *key, int key_len) /* FNV-1a hash {{{ */
{
	unsigned int hval = FNV_32_INIT;
	int i;

	for (i=0; i<key_len; i++) {
		hval ^= (unsigned int)key[i];
		hval *= FNV_32_PRIME;
	}

	return hval;
}
/* }}} */

static void mmc_pool_init_hash(mmc_pool_t *pool TSRMLS_DC) /* {{{ */
{
	mmc_hash_function hash;

	switch (MEMCACHE_G(hash_strategy)) {
		case MMC_CONSISTENT_HASH:
			pool->hash = &mmc_consistent_hash;
			break;
		default:
			pool->hash = &mmc_standard_hash;
	}

	switch (MEMCACHE_G(hash_function)) {
		case MMC_HASH_FNV1A:
			hash = &mmc_hash_fnv1a;
			break;
		default:
			hash = &mmc_hash_crc32;
	}
	
	pool->hash_state = pool->hash->create_state(hash);
}
/* }}} */

mmc_pool_t *mmc_pool_new(TSRMLS_D) /* {{{ */
{
	mmc_pool_t *pool = emalloc(sizeof(mmc_pool_t));
	pool->num_servers = 0;
	pool->compress_threshold = 0;
	pool->in_free = 0;
	pool->min_compress_savings = MMC_DEFAULT_SAVINGS;

	mmc_pool_init_hash(pool TSRMLS_CC);

	return pool;
}
/* }}} */

void mmc_pool_free(mmc_pool_t *pool TSRMLS_DC) /* {{{ */
{
	int i;

	if (pool->in_free) {
		php_error_docref(NULL TSRMLS_CC, E_ERROR, "Recursive reference detected, bailing out");
		return;
	}
	pool->in_free = 1;

	for (i=0; i<pool->num_servers; i++) {
		if (!pool->servers[i]) {
			continue;
		}
		if (pool->servers[i]->persistent == 0 && pool->servers[i]->host != NULL) {
			mmc_server_free(pool->servers[i] TSRMLS_CC);
		} else {
			mmc_server_sleep(pool->servers[i] TSRMLS_CC);
		}
		pool->servers[i] = NULL;
	}

	if (pool->num_servers) {
		efree(pool->servers);
		efree(pool->requests);
	}

	pool->hash->free_state(pool->hash_state);
	efree(pool);
}
/* }}} */

void mmc_pool_add(mmc_pool_t *pool, mmc_t *mmc, unsigned int weight) /* {{{ */
{
	/* add server and a preallocated request pointer */
	if (pool->num_servers) {
		pool->servers = erealloc(pool->servers, sizeof(mmc_t *) * (pool->num_servers + 1));
		pool->requests = erealloc(pool->requests, sizeof(mmc_t *) * (pool->num_servers + 1));
	}
	else {
		pool->servers = emalloc(sizeof(mmc_t *));
		pool->requests = emalloc(sizeof(mmc_t *));
	}

	pool->servers[pool->num_servers] = mmc;
	pool->num_servers++;

	pool->hash->add_server(pool->hash_state, mmc, weight);
}
/* }}} */

static int mmc_pool_close(mmc_pool_t *pool TSRMLS_DC) /* disconnects and removes all servers in the pool {{{ */
{
	if (pool->num_servers) {
		int i;
		
		for (i=0; i<pool->num_servers; i++) {
			if (pool->servers[i]->persistent == 0 && pool->servers[i]->host != NULL) {
				mmc_server_free(pool->servers[i] TSRMLS_CC);
			} else {
				mmc_server_sleep(pool->servers[i] TSRMLS_CC);
			}
		}

		efree(pool->servers);
		pool->servers = NULL;
		pool->num_servers = 0;

		efree(pool->requests);
		pool->requests = NULL;
		
		/* reallocate the hash strategy state */
		pool->hash->free_state(pool->hash_state);
		mmc_pool_init_hash(pool TSRMLS_CC);
	}
	
	return 1;
}
/* }}} */

int mmc_pool_store(mmc_pool_t *pool, const char *command, int command_len, const char *key, int key_len, int flags, int expire, const char *value, int value_len TSRMLS_DC) /* {{{ */
{
	mmc_t *mmc;
	char *request;
	int request_len, result = -1;
	char *key_copy = NULL, *data = NULL;

	if (key_len > MMC_KEY_MAX_SIZE) {
		key = key_copy = estrndup(key, MMC_KEY_MAX_SIZE);
		key_len = MMC_KEY_MAX_SIZE;
	}

	/* autocompress large values */
	if (pool->compress_threshold && value_len >= pool->compress_threshold) {
		flags |= MMC_COMPRESSED;
	}

	if (flags & MMC_COMPRESSED) {
		unsigned long data_len;

		if (!mmc_compress(&data, &data_len, value, value_len TSRMLS_CC)) {
			/* mmc_server_seterror(mmc, "Failed to compress data", 0); */
			return -1;
		}

		/* was enough space saved to motivate uncompress processing on get */
		if (data_len < value_len * (1 - pool->min_compress_savings)) {
			value = data;
			value_len = data_len;
		}
		else {
			flags &= ~MMC_COMPRESSED;
			efree(data);
			data = NULL;
		}
	}

	request = emalloc( 		
			command_len 		
			+ 1 /* space */ 		
			+ key_len 		
			+ 1 /* space */ 		
			+ MAX_LENGTH_OF_LONG 		
			+ 1 /* space */ 		
			+ MAX_LENGTH_OF_LONG 		
			+ 1 /* space */ 		
			+ MAX_LENGTH_OF_LONG 		
			+ sizeof("\r\n") - 1 		
			+ value_len 		
			+ sizeof("\r\n") - 1 		
			+ 1 		
			); 		

	request_len = sprintf(request, "%s %s %d %d %d\r\n", command, key, flags, expire, value_len);

	memcpy(request + request_len, value, value_len); 		
	request_len += value_len; 		

	memcpy(request + request_len, "\r\n", sizeof("\r\n") - 1); 		
	request_len += sizeof("\r\n") - 1; 		

	request[request_len] = '\0';
	
	while (result < 0 && (mmc = mmc_pool_find(pool, key, key_len TSRMLS_CC)) != NULL) {
		if ((result = mmc_server_store(mmc, request, request_len TSRMLS_CC)) < 0) {
			mmc_server_failure(mmc TSRMLS_CC);
		}
	}

	if (key_copy != NULL) {
		efree(key_copy);
	}

	if (data != NULL) {
		efree(data);
	}

	efree(request);

	return result;
}
/* }}} */

static int mmc_compress(char **result, unsigned long *result_len, const char *data, int data_len TSRMLS_DC) /* {{{ */
{
	int status, level = MEMCACHE_G(compression_level);

	*result_len = data_len + (data_len / 1000) + 25 + 1; /* some magic from zlib.c */
	*result = (char *) emalloc(*result_len);

	if (!*result) {
		return 0;
	}

	if (level >= 0) {
		status = compress2((unsigned char *) *result, result_len, (unsigned const char *) data, data_len, level);
	} else {
		status = compress((unsigned char *) *result, result_len, (unsigned const char *) data, data_len);
	}

	if (status == Z_OK) {
		*result = erealloc(*result, *result_len + 1);
		(*result)[*result_len] = '\0';
		return 1;
	}

	switch (status) {
		case Z_MEM_ERROR:
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Not enough memory to perform compression");
			break;
		case Z_BUF_ERROR:
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Not enough room in the output buffer to perform compression");
			break;
		case Z_STREAM_ERROR:
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid compression level");
			break;
		default:
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Unknown error during compression");
			break;
	}

	efree(*result);
	return 0;
}
/* }}}*/

static int mmc_uncompress(char **result, unsigned long *result_len, const char *data, int data_len) /* {{{ */
{
	int status;
	unsigned int factor = 1, maxfactor = 16;
	char *tmp1 = NULL;

	do {
		*result_len = (unsigned long)data_len * (1 << factor++);
		*result = (char *) erealloc(tmp1, *result_len);
		status = uncompress((unsigned char *) *result, result_len, (unsigned const char *) data, data_len);
		tmp1 = *result;
	} while (status == Z_BUF_ERROR && factor < maxfactor);

	if (status == Z_OK) {
		*result = erealloc(*result, *result_len + 1);
		(*result)[*result_len] = '\0';
		return 1;
	}

	efree(*result);
	return 0;
}
/* }}}*/

static int mmc_get_pool(zval *id, mmc_pool_t **pool TSRMLS_DC) /* {{{ */
{
	zval **connection;
	int resource_type;

	if (Z_TYPE_P(id) != IS_OBJECT || zend_hash_find(Z_OBJPROP_P(id), "connection", sizeof("connection"), (void **) &connection) == FAILURE) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "No servers added to memcache connection");
		return 0;
	}

	*pool = (mmc_pool_t *) zend_list_find(Z_LVAL_PP(connection), &resource_type);

	if (!*pool || resource_type != le_memcache_pool) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid Memcache->connection member variable");
		return 0;
	}

	return Z_LVAL_PP(connection);
}
/* }}} */

static int _mmc_open(mmc_t *mmc, char **error_string, int *errnum TSRMLS_DC) /* {{{ */
{
	struct timeval tv;
	char *hostname = NULL, *hash_key = NULL, *errstr = NULL;
	int	hostname_len, err = 0;

	/* close open stream */
	if (mmc->stream != NULL) {
		mmc_server_disconnect(mmc TSRMLS_CC);
	}

	if (mmc->connect_timeoutms > 0) {
		tv = _convert_timeoutms_to_ts(mmc->connect_timeoutms);
	} else {
		tv.tv_sec = mmc->timeout;
		tv.tv_usec = 0;
	}

	if (mmc->port) {
		hostname_len = spprintf(&hostname, 0, "%s:%d", mmc->host, mmc->port);
	}
	else {
		hostname_len = spprintf(&hostname, 0, "%s", mmc->host);
	}

	if (mmc->persistent) {
		spprintf(&hash_key, 0, "memcache:%s", hostname);
	}

#if PHP_API_VERSION > 20020918
	mmc->stream = php_stream_xport_create( hostname, hostname_len,
										   ENFORCE_SAFE_MODE | REPORT_ERRORS,
										   STREAM_XPORT_CLIENT | STREAM_XPORT_CONNECT,
										   hash_key, &tv, NULL, &errstr, &err);
#else
	if (mmc->persistent) {
		switch(php_stream_from_persistent_id(hash_key, &(mmc->stream) TSRMLS_CC)) {
			case PHP_STREAM_PERSISTENT_SUCCESS:
				if (php_stream_eof(mmc->stream)) {
					php_stream_pclose(mmc->stream);
					mmc->stream = NULL;
					break;
				}
			case PHP_STREAM_PERSISTENT_FAILURE:
				break;
		}
	}

	if (!mmc->stream) {
		int socktype = SOCK_STREAM;
		mmc->stream = php_stream_sock_open_host(mmc->host, mmc->port, socktype, &tv, hash_key);
	}

#endif

	efree(hostname);
	if (mmc->persistent) {
		efree(hash_key);
	}

	if (!mmc->stream) {
		MMC_DEBUG(("_mmc_open: can't open socket to host"));
		mmc_server_seterror(mmc, errstr != NULL ? errstr : "Connection failed", err);
		mmc_server_deactivate(mmc TSRMLS_CC);

		if (errstr) {
			if (error_string) {
				*error_string = errstr;
			}
			else {
				efree(errstr);
			}
		}
		if (errnum) {
			*errnum = err;
		}

		return 0;
	}

	php_stream_auto_cleanup(mmc->stream);
	php_stream_set_option(mmc->stream, PHP_STREAM_OPTION_READ_TIMEOUT, 0, &tv);
	php_stream_set_option(mmc->stream, PHP_STREAM_OPTION_WRITE_BUFFER, PHP_STREAM_BUFFER_NONE, NULL);
	php_stream_set_chunk_size(mmc->stream, MEMCACHE_G(chunk_size));

	mmc->status = MMC_STATUS_CONNECTED;

	if (mmc->error != NULL) {
		pefree(mmc->error, mmc->persistent);
		mmc->error = NULL;
	}

	return 1;
}
/* }}} */

int mmc_open(mmc_t *mmc, int force_connect, char **error_string, int *errnum TSRMLS_DC) /* {{{ */
{
	switch (mmc->status) {
		case MMC_STATUS_DISCONNECTED:
			return _mmc_open(mmc, error_string, errnum TSRMLS_CC);

		case MMC_STATUS_CONNECTED:
			return 1;

		case MMC_STATUS_UNKNOWN:
			/* check connection if needed */
			if (force_connect) {
				char *version;
				if ((version = mmc_get_version(mmc TSRMLS_CC)) == NULL && !_mmc_open(mmc, error_string, errnum TSRMLS_CC)) {
					break;
				}
				if (version) {
					efree(version);
				}
				mmc->status = MMC_STATUS_CONNECTED;
			}
			return 1;

		case MMC_STATUS_FAILED:
			if (mmc->retry_interval >= 0 && (long)time(NULL) >= mmc->failed + mmc->retry_interval) {
				if (_mmc_open(mmc, error_string, errnum TSRMLS_CC) /*&& mmc_flush(mmc, 0 TSRMLS_CC) > 0*/) {
					return 1;
				}
			}
			break;
	}
	return 0;
}
/* }}} */

static void mmc_server_disconnect(mmc_t *mmc TSRMLS_DC) /* {{{ */
{
	if (mmc->stream != NULL) {
		if (mmc->persistent) {
			php_stream_pclose(mmc->stream);
		}
		else {
			php_stream_close(mmc->stream);
		}
		mmc->stream = NULL;
	}
	mmc->status = MMC_STATUS_DISCONNECTED;
}
/* }}} */

void mmc_server_deactivate(mmc_t *mmc TSRMLS_DC) /* 	disconnect and marks the server as down {{{ */
{
	mmc_server_disconnect(mmc TSRMLS_CC);
	mmc->status = MMC_STATUS_FAILED;
	mmc->failed = (long)time(NULL);

	if (mmc->failure_callback != NULL) {
		zval *retval = NULL;
		zval *host, *tcp_port, *udp_port, *error, *errnum;
		zval **params[5];

		params[0] = &host;
		params[1] = &tcp_port;
		params[2] = &udp_port;
		params[3] = &error;
		params[4] = &errnum;

		MAKE_STD_ZVAL(host);
		MAKE_STD_ZVAL(tcp_port); MAKE_STD_ZVAL(udp_port);
		MAKE_STD_ZVAL(error); MAKE_STD_ZVAL(errnum);

		ZVAL_STRING(host, mmc->host, 1);
		ZVAL_LONG(tcp_port, mmc->port); ZVAL_LONG(udp_port, 0);
		
		if (mmc->error != NULL) {
			ZVAL_STRING(error, mmc->error, 1);
		}
		else {
			ZVAL_NULL(error);
		}
		ZVAL_LONG(errnum, mmc->errnum);

		call_user_function_ex(EG(function_table), NULL, mmc->failure_callback, &retval, 5, params, 0, NULL TSRMLS_CC);

		zval_ptr_dtor(&host);
		zval_ptr_dtor(&tcp_port); zval_ptr_dtor(&udp_port);
		zval_ptr_dtor(&error); zval_ptr_dtor(&errnum);
		
		if (retval != NULL) {
			zval_ptr_dtor(&retval);
		}
	}
	else {
		php_error_docref(NULL TSRMLS_CC, E_NOTICE, "Server %s (tcp %d) failed with: %s (%d)", 
			mmc->host, mmc->port, mmc->error, mmc->errnum);
	}
}
/* }}} */

static int mmc_readline(mmc_t *mmc TSRMLS_DC) /* {{{ */
{
	char *response;
	size_t response_len;

	if (mmc->stream == NULL) {
		mmc_server_seterror(mmc, "Socket is closed", 0);
		return -1;
	}

	response = php_stream_get_line(mmc->stream, ZSTR(mmc->inbuf), MMC_BUF_SIZE, &response_len);
	if (response) {
		MMC_DEBUG(("mmc_readline: read data:"));
		MMC_DEBUG(("mmc_readline:---"));
		MMC_DEBUG(("%s", response));
		MMC_DEBUG(("mmc_readline:---"));
		return response_len;
	}

	mmc_server_seterror(mmc, "Failed reading line from stream", 0);
	return -1;
}
/* }}} */

static char *mmc_get_version(mmc_t *mmc TSRMLS_DC) /* {{{ */
{
	char *version_str;
	int response_len;

	if (mmc_sendcmd(mmc, "version", sizeof("version") - 1 TSRMLS_CC) < 0) {
		return NULL;
	}

	if ((response_len = mmc_readline(mmc TSRMLS_CC)) < 0) {
		return NULL;
	}

	if (mmc_str_left(mmc->inbuf, "VERSION ", response_len, sizeof("VERSION ") - 1)) {
		version_str = estrndup(mmc->inbuf + sizeof("VERSION ") - 1, response_len - (sizeof("VERSION ") - 1) - (sizeof("\r\n") - 1) );
		return version_str;
	}

	mmc_server_seterror(mmc, "Malformed version string", 0);
	return NULL;
}
/* }}} */

static int mmc_str_left(char *haystack, char *needle, int haystack_len, int needle_len) /* {{{ */
{
	char *found;

	found = php_memnstr(haystack, needle, needle_len, haystack + haystack_len);
	if ((found - haystack) == 0) {
		return 1;
	}
	return 0;
}
/* }}} */

static int mmc_sendcmd(mmc_t *mmc, const char *cmd, int cmdlen TSRMLS_DC) /* {{{ */
{
	char *command;
	int command_len;
	php_netstream_data_t *sock = (php_netstream_data_t*)mmc->stream->abstract;

	if (!mmc || !cmd) {
		return -1;
	}

	MMC_DEBUG(("mmc_sendcmd: sending command '%s'", cmd));

	command = emalloc(cmdlen + sizeof("\r\n"));
	memcpy(command, cmd, cmdlen);
	memcpy(command + cmdlen, "\r\n", sizeof("\r\n") - 1);
	command_len = cmdlen + sizeof("\r\n") - 1;
	command[command_len] = '\0';

	if (mmc->timeoutms > 1) {
		sock->timeout = _convert_timeoutms_to_ts(mmc->timeoutms);
	}

	if (php_stream_write(mmc->stream, command, command_len) != command_len) {
		mmc_server_seterror(mmc, "Failed writing command to stream", 0);
		efree(command);
		return -1;
	}
	efree(command);

	return 1;
}
/* }}}*/

static int mmc_parse_response(mmc_t *mmc, char *response, int response_len, char **key, int *key_len, int *flags, int *value_len) /* {{{ */
{
	int i=0, n=0;
	int spaces[3];

	if (!response || response_len <= 0) {
		mmc_server_seterror(mmc, "Empty response", 0);
		return -1;
	}

	MMC_DEBUG(("mmc_parse_response: got response '%s'", response));

	for (i=0, n=0; i < response_len && n < 3; i++) {
		if (response[i] == ' ') {
			spaces[n++] = i;
		}
	}

	MMC_DEBUG(("mmc_parse_response: found %d spaces", n));

	if (n < 3) {
		mmc_server_seterror(mmc, "Malformed VALUE header", 0);
		return -1;
	}

	if (key != NULL) {
		int len = spaces[1] - spaces[0] - 1;

		*key = emalloc(len + 1);
		*key_len = len;

		memcpy(*key, response + spaces[0] + 1, len);
		(*key)[len] = '\0';
	}

	*flags = atoi(response + spaces[1]);
	*value_len = atoi(response + spaces[2]);

	if (*flags < 0 || *value_len < 0) {
		mmc_server_seterror(mmc, "Malformed VALUE header", 0);
		return -1;
	}

	MMC_DEBUG(("mmc_parse_response: 1st space is at %d position", spaces[1]));
	MMC_DEBUG(("mmc_parse_response: 2nd space is at %d position", spaces[2]));
	MMC_DEBUG(("mmc_parse_response: flags = %d", *flags));
	MMC_DEBUG(("mmc_parse_response: value_len = %d ", *value_len));

	return 1;
}
/* }}} */

static int mmc_postprocess_value(zval **return_value, char *value, int value_len TSRMLS_DC) /* 
	post-process a value into a result zval struct, value will be free()'ed during process {{{ */
{
	const char *value_tmp = value;
	php_unserialize_data_t var_hash;
	PHP_VAR_UNSERIALIZE_INIT(var_hash);

	if (!php_var_unserialize(return_value, (const unsigned char **)&value_tmp, (const unsigned char *)(value_tmp + value_len), &var_hash TSRMLS_CC)) {
		ZVAL_FALSE(*return_value);
		PHP_VAR_UNSERIALIZE_DESTROY(var_hash);
		efree(value);
		php_error_docref(NULL TSRMLS_CC, E_NOTICE, "unable to unserialize data");
		return 0;
	}

	PHP_VAR_UNSERIALIZE_DESTROY(var_hash);
	efree(value);
	return 1;
}
/* }}} */

int mmc_exec_retrieval_cmd(mmc_pool_t *pool, const char *key, int key_len, zval **return_value, zval *return_flags TSRMLS_DC) /* {{{ */
{
	mmc_t *mmc;
	char *command, *value;
	int result = -1, command_len, response_len, value_len, flags = 0;

	MMC_DEBUG(("mmc_exec_retrieval_cmd: key '%s'", key));

	command_len = spprintf(&command, 0, "get %s", key);

	while (result < 0 && (mmc = mmc_pool_find(pool, key, key_len TSRMLS_CC)) != NULL) {
		MMC_DEBUG(("mmc_exec_retrieval_cmd: found server '%s:%d' for key '%s'", mmc->host, mmc->port, key));

		/* send command and read value */
		if ((result = mmc_sendcmd(mmc, command, command_len TSRMLS_CC)) > 0 &&
			(result = mmc_read_value(mmc, NULL, NULL, &value, &value_len, &flags TSRMLS_CC)) >= 0) {

			/* not found */
			if (result == 0) {
				ZVAL_FALSE(*return_value);
			}
			/* read "END" */
			else if ((response_len = mmc_readline(mmc TSRMLS_CC)) < 0 || !mmc_str_left(mmc->inbuf, "END", response_len, sizeof("END")-1)) {
				mmc_server_seterror(mmc, "Malformed END line", 0);
				result = -1;
			}
			else if (flags & MMC_SERIALIZED ) {
				result = mmc_postprocess_value(return_value, value, value_len TSRMLS_CC);				
			}
			else {
				ZVAL_STRINGL(*return_value, value, value_len, 0);
			}
		}

		if (result < 0) {
			mmc_server_failure(mmc TSRMLS_CC);
		}
	}

	if (return_flags != NULL) {
		zval_dtor(return_flags);
		ZVAL_LONG(return_flags, flags);
	}
	
	efree(command);
	return result;
}
/* }}} */

static int mmc_exec_retrieval_cmd_multi(mmc_pool_t *pool, zval *keys, zval **return_value, zval *return_flags TSRMLS_DC) /* {{{ */
{
	mmc_t *mmc;
	HashPosition pos;
	zval **zkey;
	char *result_key, *value;
	char key[MMC_KEY_MAX_SIZE];
	unsigned int key_len;
	
	int	i = 0, j, num_requests, result, result_status, result_key_len, value_len, flags;
	mmc_queue_t serialized = {0};		/* mmc_queue_t<zval *>, pointers to zvals which need unserializing */

	array_init(*return_value);
	
	if (return_flags != NULL) {
		zval_dtor(return_flags);
		array_init(return_flags);
	}

	/* until no retrival errors or all servers have failed */
	do {
		result_status = num_requests = 0;
		zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(keys), &pos);

		/* first pass to build requests for each server */
		while (zend_hash_get_current_data_ex(Z_ARRVAL_P(keys), (void **)&zkey, &pos) == SUCCESS) {
			if (mmc_prepare_key(*zkey, key, &key_len TSRMLS_CC) == MMC_OK) {
				/* schedule key if first round or if missing from result */
				if ((!i || !zend_hash_exists(Z_ARRVAL_PP(return_value), key, key_len)) &&
					(mmc = mmc_pool_find(pool, key, key_len TSRMLS_CC)) != NULL) {
					if (!(mmc->outbuf.len)) {
						smart_str_appendl(&(mmc->outbuf), "get", sizeof("get")-1);
						pool->requests[num_requests++] = mmc;
					}
	
					smart_str_appendl(&(mmc->outbuf), " ", 1);
					smart_str_appendl(&(mmc->outbuf), key, key_len);
					MMC_DEBUG(("mmc_exec_retrieval_cmd_multi: scheduled key '%s' for '%s:%d' request length '%d'", key, mmc->host, mmc->port, mmc->outbuf.len));
				}
			}
			
			zend_hash_move_forward_ex(Z_ARRVAL_P(keys), &pos);
		}

		/* second pass to send requests in parallel */
		for (j=0; j<num_requests; j++) {
			smart_str_0(&(pool->requests[j]->outbuf));

			if ((result = mmc_sendcmd(pool->requests[j], pool->requests[j]->outbuf.c, pool->requests[j]->outbuf.len TSRMLS_CC)) < 0) {
				mmc_server_failure(pool->requests[j] TSRMLS_CC);
				result_status = result;
			}
		}

		/* third pass to read responses */
		for (j=0; j<num_requests; j++) {
			if (pool->requests[j]->status != MMC_STATUS_FAILED) {
				for (value = NULL; (result = mmc_read_value(pool->requests[j], &result_key, &result_key_len, &value, &value_len, &flags TSRMLS_CC)) > 0; value = NULL) {
					if (flags & MMC_SERIALIZED) {
						zval *result;
						MAKE_STD_ZVAL(result);
						ZVAL_STRINGL(result, value, value_len, 0);

						/* don't store duplicate values */
						if (zend_hash_add(Z_ARRVAL_PP(return_value), result_key, result_key_len + 1, &result, sizeof(result), NULL) == SUCCESS) {
							mmc_queue_push(&serialized, result);
						}
						else {
							zval_ptr_dtor(&result);
						}
					}
					else {
						add_assoc_stringl_ex(*return_value, result_key, result_key_len + 1, value, value_len, 0);
					}
					
					if (return_flags != NULL) {
						add_assoc_long_ex(return_flags, result_key, result_key_len + 1, flags);
					}
					
					efree(result_key);
				}

				/* check for server failure */
				if (result < 0) {
					mmc_server_failure(pool->requests[j] TSRMLS_CC);
					result_status = result;
				}
			}

			smart_str_free(&(pool->requests[j]->outbuf));
		}
	} while (result_status < 0 && MEMCACHE_G(allow_failover) && i++ < MEMCACHE_G(max_failover_attempts));
	
	/* post-process serialized values */
	if (serialized.len) {
		zval *value;
		
		while ((value = (zval *)mmc_queue_pop(&serialized)) != NULL) {
			mmc_postprocess_value(&value, Z_STRVAL_P(value), Z_STRLEN_P(value) TSRMLS_CC);
		}
		
		mmc_queue_free(&serialized);
	}

	return result_status;
}
/* }}} */

static int mmc_read_value(mmc_t *mmc, char **key, int *key_len, char **value, int *value_len, int *flags TSRMLS_DC) /* {{{ */
{
	char *data;
	int response_len, data_len, i, size;

	/* read "VALUE <key> <flags> <bytes>\r\n" header line */
	if ((response_len = mmc_readline(mmc TSRMLS_CC)) < 0) {
		MMC_DEBUG(("failed to read the server's response"));
		return -1;
	}

	/* reached the end of the data */
	if (mmc_str_left(mmc->inbuf, "END", response_len, sizeof("END") - 1)) {
		return 0;
	}

	if (mmc_parse_response(mmc, mmc->inbuf, response_len, key, key_len, flags, &data_len) < 0) {
		return -1;
	}

	MMC_DEBUG(("mmc_read_value: data len is %d bytes", data_len));

	/* data_len + \r\n + \0 */
	data = emalloc(data_len + 3);

	for (i=0; i<data_len+2; i+=size) {
		if ((size = php_stream_read(mmc->stream, data + i, data_len + 2 - i)) == 0) {
			mmc_server_seterror(mmc, "Failed reading value response body", 0);
			if (key) {
				efree(*key);
			}
			efree(data);
			return -1;
		}
	}

	data[data_len] = '\0';
	
	if ((*flags & MMC_COMPRESSED) && data_len > 0) {
		char *result_data;
		unsigned long result_len = 0;

		if (!mmc_uncompress(&result_data, &result_len, data, data_len)) {
			mmc_server_seterror(mmc, "Failed to uncompress data", 0);
			if (key) {
				efree(*key);
			}
			efree(data);
			php_error_docref(NULL TSRMLS_CC, E_NOTICE, "unable to uncompress data");
			return 0;
		}

		efree(data);
		data = result_data;
		data_len = result_len;
	}

	*value = data;
	*value_len = data_len;
	return 1;
}
/* }}} */

int mmc_delete(mmc_t *mmc, const char *key, int key_len, int time TSRMLS_DC) /* {{{ */
{
	char *command;
	int command_len, response_len;

	command_len = spprintf(&command, 0, "delete %s %d", key, time);

	MMC_DEBUG(("mmc_delete: trying to delete '%s'", key));

	if (mmc_sendcmd(mmc, command, command_len TSRMLS_CC) < 0) {
		efree(command);
		return -1;
	}
	efree(command);

	if ((response_len = mmc_readline(mmc TSRMLS_CC)) < 0){
		MMC_DEBUG(("failed to read the server's response"));
		return -1;
	}

	MMC_DEBUG(("mmc_delete: server's response is '%s'", mmc->inbuf));

	if(mmc_str_left(mmc->inbuf,"DELETED", response_len, sizeof("DELETED") - 1)) {
		return 1;
	}

	if(mmc_str_left(mmc->inbuf,"NOT_FOUND", response_len, sizeof("NOT_FOUND") - 1)) {
		return 0;
	}

	mmc_server_received_error(mmc, response_len);
	return -1;
}
/* }}} */

static int mmc_flush(mmc_t *mmc, int timestamp TSRMLS_DC) /* {{{ */
{
	char *command;
	int command_len, response_len;

	MMC_DEBUG(("mmc_flush: flushing the cache"));

	if (timestamp) {
		command_len = spprintf(&command, 0, "flush_all %d", timestamp);
	}
	else {
		command_len = spprintf(&command, 0, "flush_all");
	}

	if (mmc_sendcmd(mmc, command, command_len TSRMLS_CC) < 0) {
		efree(command);
		return -1;
	}
	efree(command);

	/* get server's response */
	if ((response_len = mmc_readline(mmc TSRMLS_CC)) < 0){
		return -1;
	}

	MMC_DEBUG(("mmc_flush: server's response is '%s'", mmc->inbuf));

	if(mmc_str_left(mmc->inbuf, "OK", response_len, sizeof("OK") - 1)) {
		return 1;
	}

	mmc_server_received_error(mmc, response_len);
	return -1;
}
/* }}} */

/*
 * STAT 6:chunk_size 64
 */
static int mmc_stats_parse_stat(char *start, char *end, zval *result TSRMLS_DC)  /* {{{ */
{
	char *space, *colon, *key;
	long index = 0;

	/* find space delimiting key and value */
	if ((space = php_memnstr(start, " ", 1, end)) == NULL) {
		return 0;
	}

	/* find colon delimiting subkeys */
	if ((colon = php_memnstr(start, ":", 1, space - 1)) != NULL) {
		zval *element, **elem;
		key = estrndup(start, colon - start);

		/* find existing or create subkey array in result */
		if ((is_numeric_string(key, colon - start, &index, NULL, 0) &&
			zend_hash_index_find(Z_ARRVAL_P(result), index, (void **) &elem) != FAILURE) ||
			zend_hash_find(Z_ARRVAL_P(result), key, colon - start + 1, (void **) &elem) != FAILURE) {
			element = *elem;
		}
		else {
			MAKE_STD_ZVAL(element);
			array_init(element);
			add_assoc_zval_ex(result, key, colon - start + 1, element);
		}

		efree(key);
		return mmc_stats_parse_stat(colon + 1, end, element TSRMLS_CC);
	}

	/* no more subkeys, add value under last subkey */
	key = estrndup(start, space - start);
	add_assoc_stringl_ex(result, key, space - start + 1, space + 1, end - space, 1);
	efree(key);

	return 1;
}
/* }}} */

/*
 * ITEM test_key [3 b; 1157099416 s]
 */
static int mmc_stats_parse_item(char *start, char *end, zval *result TSRMLS_DC)  /* {{{ */
{
	char *space, *value, *value_end, *key;
	zval *element;

	/* find space delimiting key and value */
	if ((space = php_memnstr(start, " ", 1, end)) == NULL) {
		return 0;
	}

	MAKE_STD_ZVAL(element);
	array_init(element);

	/* parse each contained value */
	for (value = php_memnstr(space, "[", 1, end); value != NULL && value <= end; value = php_memnstr(value + 1, ";", 1, end)) {
		do {
			value++;
		} while (*value == ' ' && value <= end);

		if (value <= end && (value_end = php_memnstr(value, " ", 1, end)) != NULL && value_end <= end) {
			add_next_index_stringl(element, value, value_end - value, 1);
		}
	}

	/* add parsed values under key */
	key = estrndup(start, space - start);
	add_assoc_zval_ex(result, key, space - start + 1, element);
	efree(key);

	return 1;
}
/* }}} */

static int mmc_stats_parse_generic(char *start, char *end, zval *result TSRMLS_DC)  /* {{{ */
{
	char *space, *key;

	/* "stats maps" returns "\n" delimited lines, other commands uses "\r\n" */
	if (*end == '\r') {
		end--;
	}

	if (start <= end) {
		if ((space = php_memnstr(start, " ", 1, end)) != NULL) {
			key = estrndup(start, space - start);
			add_assoc_stringl_ex(result, key, space - start + 1, space + 1, end - space, 1);
			efree(key);
		}
		else {
			add_next_index_stringl(result, start, end - start, 1);
		}
	}

	return 1;
}
/* }}} */

static int mmc_get_stats(mmc_t *mmc, char *type, int slabid, int limit, zval *result TSRMLS_DC) /* {{{ */
{
	char *command;
	int command_len, response_len;

	if (slabid) {
		command_len = spprintf(&command, 0, "stats %s %d %d", type, slabid, limit);
	}
	else if (type) {
		command_len = spprintf(&command, 0, "stats %s", type);
	}
	else {
		command_len = spprintf(&command, 0, "stats");
	}

	if (mmc_sendcmd(mmc, command, command_len TSRMLS_CC) < 0) {
		efree(command);
		return -1;
	}

	efree(command);
	array_init(result);

	while ((response_len = mmc_readline(mmc TSRMLS_CC)) >= 0) {
		if (mmc_str_left(mmc->inbuf, "ERROR", response_len, sizeof("ERROR") - 1) ||
			mmc_str_left(mmc->inbuf, "CLIENT_ERROR", response_len, sizeof("CLIENT_ERROR") - 1) ||
			mmc_str_left(mmc->inbuf, "SERVER_ERROR", response_len, sizeof("SERVER_ERROR") - 1)) {

			zend_hash_destroy(Z_ARRVAL_P(result));
			FREE_HASHTABLE(Z_ARRVAL_P(result));

			ZVAL_FALSE(result);
			return 0;
		}
		else if (mmc_str_left(mmc->inbuf, "RESET", response_len, sizeof("RESET") - 1)) {
			zend_hash_destroy(Z_ARRVAL_P(result));
			FREE_HASHTABLE(Z_ARRVAL_P(result));

			ZVAL_TRUE(result);
			return 1;
		}
		else if (mmc_str_left(mmc->inbuf, "ITEM ", response_len, sizeof("ITEM ") - 1)) {
			if (!mmc_stats_parse_item(mmc->inbuf + (sizeof("ITEM ") - 1), mmc->inbuf + response_len - sizeof("\r\n"), result TSRMLS_CC)) {
				zend_hash_destroy(Z_ARRVAL_P(result));
				FREE_HASHTABLE(Z_ARRVAL_P(result));
				return -1;
			}
		}
		else if (mmc_str_left(mmc->inbuf, "STAT ", response_len, sizeof("STAT ") - 1)) {
			if (!mmc_stats_parse_stat(mmc->inbuf + (sizeof("STAT ") - 1), mmc->inbuf + response_len - sizeof("\r\n"), result TSRMLS_CC)) {
				zend_hash_destroy(Z_ARRVAL_P(result));
				FREE_HASHTABLE(Z_ARRVAL_P(result));
				return -1;
			}
		}
		else if (mmc_str_left(mmc->inbuf, "END", response_len, sizeof("END") - 1)) {
			break;
		}
		else if (!mmc_stats_parse_generic(mmc->inbuf, mmc->inbuf + response_len - sizeof("\n"), result TSRMLS_CC)) {
			zend_hash_destroy(Z_ARRVAL_P(result));
			FREE_HASHTABLE(Z_ARRVAL_P(result));
			return -1;
		}
	}

	if (response_len < 0) {
		zend_hash_destroy(Z_ARRVAL_P(result));
		FREE_HASHTABLE(Z_ARRVAL_P(result));
		return -1;
	}

	return 1;
}
/* }}} */

static int mmc_incr_decr(mmc_t *mmc, int cmd, char *key, int key_len, int value, long *number TSRMLS_DC) /* {{{ */
{
	char *command;
	int  command_len, response_len;

	if (cmd > 0) {
		command_len = spprintf(&command, 0, "incr %s %d", key, value);
	}
	else {
		command_len = spprintf(&command, 0, "decr %s %d", key, value);
	}

	if (mmc_sendcmd(mmc, command, command_len TSRMLS_CC) < 0) {
		efree(command);
		return -1;
	}
	efree(command);

	if ((response_len = mmc_readline(mmc TSRMLS_CC)) < 0) {
		MMC_DEBUG(("failed to read the server's response"));
		return -1;
	}

	MMC_DEBUG(("mmc_incr_decr: server's answer is: '%s'", mmc->inbuf));
	if (mmc_str_left(mmc->inbuf, "NOT_FOUND", response_len, sizeof("NOT_FOUND") - 1)) {
		MMC_DEBUG(("failed to %sement variable - item with such key not found", cmd > 0 ? "incr" : "decr"));
		return 0;
	}
	else if (mmc_str_left(mmc->inbuf, "ERROR", response_len, sizeof("ERROR") - 1) ||
			 mmc_str_left(mmc->inbuf, "CLIENT_ERROR", response_len, sizeof("CLIENT_ERROR") - 1) ||
			 mmc_str_left(mmc->inbuf, "SERVER_ERROR", response_len, sizeof("SERVER_ERROR") - 1)) {
		mmc_server_received_error(mmc, response_len);
		return -1;
	}

	*number = (long)atol(mmc->inbuf);
	return 1;
}
/* }}} */

static void php_mmc_store(INTERNAL_FUNCTION_PARAMETERS, char *command, int command_len) /* {{{ */
{
	mmc_pool_t *pool;
	zval *value, *mmc_object = getThis();

	int result, key_len;
	char *key;
	long flags = 0, expire = 0;
	char key_tmp[MMC_KEY_MAX_SIZE];
	unsigned int key_tmp_len;

	php_serialize_data_t value_hash;
	smart_str buf = {0};

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Osz|ll", &mmc_object, memcache_class_entry_ptr, &key, &key_len, &value, &flags, &expire) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sz|ll", &key, &key_len, &value, &flags, &expire) == FAILURE) {
			return;
		}
	}

	if (mmc_prepare_key_ex(key, key_len, key_tmp, &key_tmp_len TSRMLS_CC) != MMC_OK) {
		RETURN_FALSE;
	}
	
	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC) || !pool->num_servers) {
		RETURN_FALSE;
	}

	switch (Z_TYPE_P(value)) {
		case IS_STRING:
			result = mmc_pool_store(
				pool, command, command_len, key_tmp, key_tmp_len, flags, expire, 
				Z_STRVAL_P(value), Z_STRLEN_P(value) TSRMLS_CC);
			break;

		case IS_LONG:
		case IS_DOUBLE:
		case IS_BOOL: {
			zval value_copy;

			/* FIXME: we should be using 'Z' instead of this, but unfortunately it's PHP5-only */
			value_copy = *value;
			zval_copy_ctor(&value_copy);
			convert_to_string(&value_copy);

			result = mmc_pool_store(
				pool, command, command_len, key_tmp, key_tmp_len, flags, expire, 
				Z_STRVAL(value_copy), Z_STRLEN(value_copy) TSRMLS_CC);

			zval_dtor(&value_copy);
			break;
		}

		default: {
			zval value_copy, *value_copy_ptr;

			/* FIXME: we should be using 'Z' instead of this, but unfortunately it's PHP5-only */
			value_copy = *value;
			zval_copy_ctor(&value_copy);
			value_copy_ptr = &value_copy;

			PHP_VAR_SERIALIZE_INIT(value_hash);
			php_var_serialize(&buf, &value_copy_ptr, &value_hash TSRMLS_CC);
			PHP_VAR_SERIALIZE_DESTROY(value_hash);

			if (!buf.c) {
				/* something went really wrong */
				zval_dtor(&value_copy);
				php_error_docref(NULL TSRMLS_CC, E_WARNING, "Failed to serialize value");
				RETURN_FALSE;
			}

			flags |= MMC_SERIALIZED;
			zval_dtor(&value_copy);

			result = mmc_pool_store(
				pool, command, command_len, key_tmp, key_tmp_len, flags, expire, 
				buf.c, buf.len TSRMLS_CC);
		}
	}

	if (flags & MMC_SERIALIZED) {
		smart_str_free(&buf);
	}

	if (result > 0) {
		RETURN_TRUE;
	}

	RETURN_FALSE;
}
/* }}} */

static void php_mmc_incr_decr(INTERNAL_FUNCTION_PARAMETERS, int cmd) /* {{{ */
{
	mmc_t *mmc;
	mmc_pool_t *pool;
	int result = -1, key_len;
	long value = 1, number;
	char *key;
	zval *mmc_object = getThis();
	char key_tmp[MMC_KEY_MAX_SIZE];
	unsigned int key_tmp_len;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Os|l", &mmc_object, memcache_class_entry_ptr, &key, &key_len, &value) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|l", &key, &key_len, &value) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC) || !pool->num_servers) {
		RETURN_FALSE;
	}

	if (mmc_prepare_key_ex(key, key_len, key_tmp, &key_tmp_len TSRMLS_CC) != MMC_OK) {
		RETURN_FALSE;
	}

	while (result < 0 && (mmc = mmc_pool_find(pool, key_tmp, key_tmp_len TSRMLS_CC)) != NULL) {
		if ((result = mmc_incr_decr(mmc, cmd, key_tmp, key_tmp_len, value, &number TSRMLS_CC)) < 0) {
			mmc_server_failure(mmc TSRMLS_CC);
		}
	}

	if (result > 0) {
		RETURN_LONG(number);
	}
	RETURN_FALSE;
}
/* }}} */

static void php_mmc_connect (INTERNAL_FUNCTION_PARAMETERS, int persistent) /* {{{ */
{
	zval **connection, *mmc_object = getThis();
	mmc_t *mmc = NULL;
	mmc_pool_t *pool;
	int resource_type, host_len, errnum = 0, list_id;
	char *host, *error_string = NULL;
	long port = MEMCACHE_G(default_port), timeout = MMC_DEFAULT_TIMEOUT, timeoutms = 0;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|lll", &host, &host_len, &port, &timeout, &timeoutms) == FAILURE) {
		return;
	}

	if (timeoutms < 1) { 
		timeoutms = MEMCACHE_G(default_timeout_ms);
	}

	/* initialize and connect server struct */
	if (persistent) {
		mmc = mmc_find_persistent(host, host_len, port, timeout, MMC_DEFAULT_RETRY TSRMLS_CC);
	}
	else {
		MMC_DEBUG(("php_mmc_connect: creating regular connection"));
		mmc = mmc_server_new(host, host_len, port, 0, timeout, MMC_DEFAULT_RETRY TSRMLS_CC);
	}

	mmc->timeout = timeout;
	mmc->connect_timeoutms = timeoutms;

	if (!mmc_open(mmc, 1, &error_string, &errnum TSRMLS_CC)) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Can't connect to %s:%ld, %s (%d)", host, port, error_string ? error_string : "Unknown error", errnum);
		if (!persistent) {
			mmc_server_free(mmc TSRMLS_CC);
		}
		if (error_string) {
			efree(error_string);
		}
		RETURN_FALSE;
	}

	/* initialize pool and object if need be */
	if (!mmc_object) {
		pool = mmc_pool_new(TSRMLS_C);
		mmc_pool_add(pool, mmc, 1);

		object_init_ex(return_value, memcache_class_entry_ptr);
		list_id = zend_list_insert(pool, le_memcache_pool);
		add_property_resource(return_value, "connection", list_id);
	}
	else if (zend_hash_find(Z_OBJPROP_P(mmc_object), "connection", sizeof("connection"), (void **) &connection) != FAILURE) {
		pool = (mmc_pool_t *) zend_list_find(Z_LVAL_PP(connection), &resource_type);
		if (!pool || resource_type != le_memcache_pool) {
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Unknown connection identifier");
			RETURN_FALSE;
		}

		mmc_pool_add(pool, mmc, 1);
		RETURN_TRUE;
	}
	else {
		pool = mmc_pool_new(TSRMLS_C);
		mmc_pool_add(pool, mmc, 1);

		list_id = zend_list_insert(pool, le_memcache_pool);
		add_property_resource(mmc_object, "connection", list_id);
		RETURN_TRUE;
	}
}
/* }}} */

/* ----------------
   module functions
   ---------------- */

/* {{{ proto object memcache_connect( string host [, int port [, int timeout ] ])
   Connects to server and returns a Memcache object */
PHP_FUNCTION(memcache_connect)
{
	php_mmc_connect(INTERNAL_FUNCTION_PARAM_PASSTHRU, 0);
}
/* }}} */

/* {{{ proto object memcache_pconnect( string host [, int port [, int timeout ] ])
   Connects to server and returns a Memcache object */
PHP_FUNCTION(memcache_pconnect)
{
	php_mmc_connect(INTERNAL_FUNCTION_PARAM_PASSTHRU, 1);
}
/* }}} */

/* {{{ proto bool memcache_add_server( string host [, int port [, bool persistent [, int weight [, int timeout [, int retry_interval [, bool status [, callback failure_callback ] ] ] ] ] ] ])
   Adds a connection to the pool. The order in which this function is called is significant */
PHP_FUNCTION(memcache_add_server)
{
	zval **connection, *mmc_object = getThis(), *failure_callback = NULL;
	mmc_pool_t *pool;
	mmc_t *mmc;
	long port = MEMCACHE_G(default_port), weight = 1, timeout = MMC_DEFAULT_TIMEOUT, retry_interval = MMC_DEFAULT_RETRY, timeoutms = 0;
	zend_bool persistent = 1, status = 1;
	int resource_type, host_len, list_id;
	char *host;

	if (mmc_object) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|lblllbzl", &host, &host_len, &port, &persistent, &weight, &timeout, &retry_interval, &status, &failure_callback, &timeoutms) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Os|lblllbzl", &mmc_object, memcache_class_entry_ptr, &host, &host_len, &port, &persistent, &weight, &timeout, &retry_interval, &status, &failure_callback, &timeoutms) == FAILURE) {
			return;
		}
	}

	if (timeoutms < 1) { 
		timeoutms = MEMCACHE_G(default_timeout_ms);
	}

	if (weight < 1) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "weight must be a positive integer");
		RETURN_FALSE;
	}

	if (failure_callback != NULL && Z_TYPE_P(failure_callback) != IS_NULL) {
		if (!IS_CALLABLE(failure_callback, 0, NULL)) {
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid failure callback");
			RETURN_FALSE;
		}
	}

	/* lazy initialization of server struct */
	if (persistent) {
		mmc = mmc_find_persistent(host, host_len, port, timeout, retry_interval TSRMLS_CC);
	}
	else {
		MMC_DEBUG(("memcache_add_server: initializing regular struct"));
		mmc = mmc_server_new(host, host_len, port, 0, timeout, retry_interval TSRMLS_CC);
	}

	mmc->connect_timeoutms = timeoutms;

	/* add server in failed mode */
	if (!status) {
		mmc->status = MMC_STATUS_FAILED;
	}

	if (failure_callback != NULL && Z_TYPE_P(failure_callback) != IS_NULL) {
		mmc->failure_callback = failure_callback;
		mmc_server_callback_ctor(&mmc->failure_callback TSRMLS_CC);
	}

	/* initialize pool if need be */
	if (zend_hash_find(Z_OBJPROP_P(mmc_object), "connection", sizeof("connection"), (void **) &connection) == FAILURE) {
		pool = mmc_pool_new(TSRMLS_C);
		list_id = zend_list_insert(pool, le_memcache_pool);
		add_property_resource(mmc_object, "connection", list_id);
	}
	else {
		pool = (mmc_pool_t *) zend_list_find(Z_LVAL_PP(connection), &resource_type);
		if (!pool || resource_type != le_memcache_pool) {
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Failed to extract 'connection' variable from object");
			RETURN_FALSE;
		}
	}

	mmc_pool_add(pool, mmc, weight);
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool memcache_set_server_params( string host [, int port [, int timeout [, int retry_interval [, bool status [, callback failure_callback ] ] ] ] ])
   Changes server parameters at runtime */
PHP_FUNCTION(memcache_set_server_params)
{
	zval *mmc_object = getThis(), *failure_callback = NULL;
	mmc_pool_t *pool;
	mmc_t *mmc = NULL;
	long port = MEMCACHE_G(default_port), timeout = MMC_DEFAULT_TIMEOUT, retry_interval = MMC_DEFAULT_RETRY;
	zend_bool status = 1;
	int host_len, i;
	char *host;

	if (mmc_object) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|lllbz", &host, &host_len, &port, &timeout, &retry_interval, &status, &failure_callback) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Os|lllbz", &mmc_object, memcache_class_entry_ptr, &host, &host_len, &port, &timeout, &retry_interval, &status, &failure_callback) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	for (i=0; i<pool->num_servers; i++) {
		if (!strcmp(pool->servers[i]->host, host) && pool->servers[i]->port == port) {
			mmc = pool->servers[i];
			break;
		}
	}

	if (!mmc) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Server not found in pool");
		RETURN_FALSE;
	}

	if (failure_callback != NULL && Z_TYPE_P(failure_callback) != IS_NULL) {
		if (!IS_CALLABLE(failure_callback, 0, NULL)) {
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid failure callback");
			RETURN_FALSE;
		}
	}

	mmc->timeout = timeout;
	mmc->retry_interval = retry_interval;

	if (!status) {
		mmc->status = MMC_STATUS_FAILED;
	}
	else if (mmc->status == MMC_STATUS_FAILED) {
		mmc->status = MMC_STATUS_DISCONNECTED;
	}

	if (failure_callback != NULL) {
		if (mmc->failure_callback != NULL) {
			mmc_server_callback_dtor(&mmc->failure_callback TSRMLS_CC);
		}

		if (Z_TYPE_P(failure_callback) != IS_NULL) {
			mmc->failure_callback = failure_callback;
			mmc_server_callback_ctor(&mmc->failure_callback TSRMLS_CC);
		}
		else {
			mmc->failure_callback = NULL;
		}
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto int memcache_get_server_status( string host [, int port ])
   Returns server status (0 if server is failed, otherwise non-zero) */
PHP_FUNCTION(memcache_get_server_status)
{
	zval *mmc_object = getThis();
	mmc_pool_t *pool;
	mmc_t *mmc = NULL;
	long port = MEMCACHE_G(default_port);
	int host_len, i;
	char *host;

	if (mmc_object) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|l", &host, &host_len, &port) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Os|l", &mmc_object, memcache_class_entry_ptr, &host, &host_len, &port) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	for (i=0; i<pool->num_servers; i++) {
		if (!strcmp(pool->servers[i]->host, host) && pool->servers[i]->port == port) {
			mmc = pool->servers[i];
			break;
		}
	}

	if (!mmc) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Server not found in pool");
		RETURN_FALSE;
	}

	RETURN_LONG(mmc->status);
}
/* }}} */

mmc_t *mmc_find_persistent(char *host, int host_len, int port, int timeout, int retry_interval TSRMLS_DC) /* {{{ */
{
	mmc_t *mmc;
	zend_rsrc_list_entry *le;
	char *hash_key;
	int hash_key_len;

	MMC_DEBUG(("mmc_find_persistent: seeking for persistent connection"));
	hash_key_len = spprintf(&hash_key, 0, "mmc_connect___%s:%d", host, port);

	if (zend_hash_find(&EG(persistent_list), hash_key, hash_key_len+1, (void **) &le) == FAILURE) {
		zend_rsrc_list_entry new_le;
		MMC_DEBUG(("mmc_find_persistent: connection wasn't found in the hash"));

		mmc = mmc_server_new(host, host_len, port, 1, timeout, retry_interval TSRMLS_CC);
		new_le.type = le_pmemcache;
		new_le.ptr  = mmc;

		/* register new persistent connection */
		if (zend_hash_update(&EG(persistent_list), hash_key, hash_key_len+1, (void *) &new_le, sizeof(zend_rsrc_list_entry), NULL) == FAILURE) {
			mmc_server_free(mmc TSRMLS_CC);
			mmc = NULL;
		} else {
			zend_list_insert(mmc, le_pmemcache);
		}
	}
	else if (le->type != le_pmemcache || le->ptr == NULL) {
		zend_rsrc_list_entry new_le;
		MMC_DEBUG(("mmc_find_persistent: something was wrong, reconnecting.."));
		zend_hash_del(&EG(persistent_list), hash_key, hash_key_len+1);

		mmc = mmc_server_new(host, host_len, port, 1, timeout, retry_interval TSRMLS_CC);
		new_le.type = le_pmemcache;
		new_le.ptr  = mmc;

		/* register new persistent connection */
		if (zend_hash_update(&EG(persistent_list), hash_key, hash_key_len+1, (void *) &new_le, sizeof(zend_rsrc_list_entry), NULL) == FAILURE) {
			mmc_server_free(mmc TSRMLS_CC);
			mmc = NULL;
		}
		else {
			zend_list_insert(mmc, le_pmemcache);
		}
	}
	else {
		MMC_DEBUG(("mmc_find_persistent: connection found in the hash"));
		mmc = (mmc_t *)le->ptr;
		mmc->timeout = timeout;
		mmc->retry_interval = retry_interval;

		/* attempt to reconnect this node before failover in case connection has gone away */
		if (mmc->status == MMC_STATUS_CONNECTED) {
			mmc->status = MMC_STATUS_UNKNOWN;
		}
	}

	efree(hash_key);
	return mmc;
}
/* }}} */

/* {{{ proto string memcache_get_version( object memcache )
   Returns server's version */
PHP_FUNCTION(memcache_get_version)
{
	mmc_pool_t *pool;
	char *version;
	int i;
	zval *mmc_object = getThis();

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "O", &mmc_object, memcache_class_entry_ptr) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	for (i=0; i<pool->num_servers; i++) {
		if (mmc_open(pool->servers[i], 1, NULL, NULL TSRMLS_CC)) {
			if ((version = mmc_get_version(pool->servers[i] TSRMLS_CC)) != NULL) {
				RETURN_STRING(version, 0);
			}
			else {
				mmc_server_failure(pool->servers[i] TSRMLS_CC);
			}
		}
	}

	RETURN_FALSE;
}
/* }}} */

/* {{{ proto bool memcache_add( object memcache, string key, mixed var [, int flag [, int expire ] ] )
   Adds new item. Item with such key should not exist. */
PHP_FUNCTION(memcache_add)
{
	php_mmc_store(INTERNAL_FUNCTION_PARAM_PASSTHRU, "add", sizeof("add") - 1);
}
/* }}} */

/* {{{ proto bool memcache_set( object memcache, string key, mixed var [, int flag [, int expire ] ] )
   Sets the value of an item. Item may exist or not */
PHP_FUNCTION(memcache_set)
{
	php_mmc_store(INTERNAL_FUNCTION_PARAM_PASSTHRU, "set", sizeof("set") - 1);
}
/* }}} */

/* {{{ proto bool memcache_replace( object memcache, string key, mixed var [, int flag [, int expire ] ] )
   Replaces existing item. Returns false if item doesn't exist */
PHP_FUNCTION(memcache_replace)
{
	php_mmc_store(INTERNAL_FUNCTION_PARAM_PASSTHRU, "replace", sizeof("replace") - 1);
}
/* }}} */

/* {{{ proto mixed memcache_get( object memcache, mixed key [, mixed &flags ] )
   Returns value of existing item or false */
PHP_FUNCTION(memcache_get)
{
	mmc_pool_t *pool;
	zval *zkey, *mmc_object = getThis(), *flags = NULL;
	char key[MMC_KEY_MAX_SIZE];
	unsigned int key_len;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Oz|z", &mmc_object, memcache_class_entry_ptr, &zkey, &flags) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &zkey, &flags) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC) || !pool->num_servers) {
		RETURN_FALSE;
	}

	if (Z_TYPE_P(zkey) != IS_ARRAY) {
		if (mmc_prepare_key(zkey, key, &key_len TSRMLS_CC) == MMC_OK) {
			if (mmc_exec_retrieval_cmd(pool, key, key_len, &return_value, flags TSRMLS_CC) < 0) {
				zval_dtor(return_value);
				RETVAL_FALSE;
			}
		}
		else {
			RETVAL_FALSE;
		}
	} else if (zend_hash_num_elements(Z_ARRVAL_P(zkey))){
		if (mmc_exec_retrieval_cmd_multi(pool, zkey, &return_value, flags TSRMLS_CC) < 0) {
			zval_dtor(return_value);
			RETVAL_FALSE;
		}
	} else {
		RETVAL_FALSE;
	}
}
/* }}} */

/* {{{ proto bool memcache_delete( object memcache, string key [, int expire ])
   Deletes existing item */
PHP_FUNCTION(memcache_delete)
{
	mmc_t *mmc;
	mmc_pool_t *pool;
	int result = -1, key_len;
	zval *mmc_object = getThis();
	char *key;
	long time = 0;
	char key_tmp[MMC_KEY_MAX_SIZE];
	unsigned int key_tmp_len;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Os|l", &mmc_object, memcache_class_entry_ptr, &key, &key_len, &time) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|l", &key, &key_len, &time) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC) || !pool->num_servers) {
		RETURN_FALSE;
	}

	if (mmc_prepare_key_ex(key, key_len, key_tmp, &key_tmp_len TSRMLS_CC) != MMC_OK) {
		RETURN_FALSE;
	}

	while (result < 0 && (mmc = mmc_pool_find(pool, key_tmp, key_tmp_len TSRMLS_CC)) != NULL) {
		if ((result = mmc_delete(mmc, key_tmp, key_tmp_len, time TSRMLS_CC)) < 0) {
			mmc_server_failure(mmc TSRMLS_CC);
		}
	}

	if (result > 0) {
		RETURN_TRUE;
	}
	RETURN_FALSE;
}
/* }}} */

/* {{{ proto bool memcache_debug( bool onoff )
   Turns on/off internal debugging */
PHP_FUNCTION(memcache_debug)
{
#if ZEND_DEBUG
	zend_bool onoff;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "b", &onoff) == FAILURE) {
		return;
	}

	MEMCACHE_G(debug_mode) = onoff ? 1 : 0;

	RETURN_TRUE;
#else
	RETURN_FALSE;
#endif

}
/* }}} */

/* {{{ proto array memcache_get_stats( object memcache [, string type [, int slabid [, int limit ] ] ])
   Returns server's statistics */
PHP_FUNCTION(memcache_get_stats)
{
	mmc_pool_t *pool;
	int i, failures = 0;
	zval *mmc_object = getThis();

	char *type = NULL;
	int type_len = 0;
	long slabid = 0, limit = MMC_DEFAULT_CACHEDUMP_LIMIT;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "O|sll", &mmc_object, memcache_class_entry_ptr, &type, &type_len, &slabid, &limit) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|sll", &type, &type_len, &slabid, &limit) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	for (i=0; i<pool->num_servers; i++) {
		if (mmc_open(pool->servers[i], 1, NULL, NULL TSRMLS_CC)) {
			if (mmc_get_stats(pool->servers[i], type, slabid, limit, return_value TSRMLS_CC) < 0) {
				mmc_server_failure(pool->servers[i] TSRMLS_CC);
				failures++;
			}
			else {
				break;
			}
		}
		else {
			failures++;
		}
	}

	if (failures >= pool->num_servers) {
		RETURN_FALSE;
	}
}
/* }}} */

/* {{{ proto array memcache_get_extended_stats( object memcache [, string type [, int slabid [, int limit ] ] ])
   Returns statistics for each server in the pool */
PHP_FUNCTION(memcache_get_extended_stats)
{
	mmc_pool_t *pool;
	char *hostname;
	int i, hostname_len;
	zval *mmc_object = getThis(), *stats;

	char *type = NULL;
	int type_len = 0;
	long slabid = 0, limit = MMC_DEFAULT_CACHEDUMP_LIMIT;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "O|sll", &mmc_object, memcache_class_entry_ptr, &type, &type_len, &slabid, &limit) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|sll", &type, &type_len, &slabid, &limit) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	array_init(return_value);
	for (i=0; i<pool->num_servers; i++) {
		MAKE_STD_ZVAL(stats);

		hostname_len = spprintf(&hostname, 0, "%s:%d", pool->servers[i]->host, pool->servers[i]->port);

		if (mmc_open(pool->servers[i], 1, NULL, NULL TSRMLS_CC)) {
			if (mmc_get_stats(pool->servers[i], type, slabid, limit, stats TSRMLS_CC) < 0) {
				mmc_server_failure(pool->servers[i] TSRMLS_CC);
				ZVAL_FALSE(stats);
			}
		}
		else {
			ZVAL_FALSE(stats);
		}

		add_assoc_zval_ex(return_value, hostname, hostname_len + 1, stats);
		efree(hostname);
	}
}
/* }}} */

/* {{{ proto array memcache_set_compress_threshold( object memcache, int threshold [, float min_savings ] )
   Set automatic compress threshold */
PHP_FUNCTION(memcache_set_compress_threshold)
{
	mmc_pool_t *pool;
	zval *mmc_object = getThis();
	long threshold;
	double min_savings = MMC_DEFAULT_SAVINGS;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Ol|d", &mmc_object, memcache_class_entry_ptr, &threshold, &min_savings) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l|d", &threshold, &min_savings) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	if (threshold < 0) {
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "threshold must be a positive integer");
		RETURN_FALSE;
	}
	pool->compress_threshold = threshold;

	if (min_savings != MMC_DEFAULT_SAVINGS) {
		if (min_savings < 0 || min_savings > 1) {
			php_error_docref(NULL TSRMLS_CC, E_WARNING, "min_savings must be a float in the 0..1 range");
			RETURN_FALSE;
		}
		pool->min_compress_savings = min_savings;
	}
	else {
		pool->min_compress_savings = MMC_DEFAULT_SAVINGS;
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto int memcache_increment( object memcache, string key [, int value ] )
   Increments existing variable */
PHP_FUNCTION(memcache_increment)
{
	php_mmc_incr_decr(INTERNAL_FUNCTION_PARAM_PASSTHRU, 1);
}
/* }}} */

/* {{{ proto int memcache_decrement( object memcache, string key [, int value ] )
   Decrements existing variable */
PHP_FUNCTION(memcache_decrement)
{
	php_mmc_incr_decr(INTERNAL_FUNCTION_PARAM_PASSTHRU, 0);
}
/* }}} */

/* {{{ proto bool memcache_close( object memcache )
   Closes connection to memcached */
PHP_FUNCTION(memcache_close)
{
	mmc_pool_t *pool;
	zval *mmc_object = getThis();

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "O", &mmc_object, memcache_class_entry_ptr) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	if (!mmc_pool_close(pool TSRMLS_CC)) {
		RETURN_FALSE;
	}
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool memcache_flush( object memcache [, int timestamp ] )
   Flushes cache, optionally at the specified time */
PHP_FUNCTION(memcache_flush)
{
	mmc_pool_t *pool;
	int i, failures = 0;
	zval *mmc_object = getThis();
	long timestamp = 0;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "O|l", &mmc_object, memcache_class_entry_ptr, &timestamp) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|l", &timestamp) == FAILURE) {
			return;
		}
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	for (i=0; i<pool->num_servers; i++) {
		if (mmc_open(pool->servers[i], 1, NULL, NULL TSRMLS_CC)) {
			if (mmc_flush(pool->servers[i], timestamp TSRMLS_CC) < 0) {
				mmc_server_failure(pool->servers[i] TSRMLS_CC);
				failures++;
			}
		}
		else {
			failures++;
		}
	}

	if (failures && failures >= pool->num_servers) {
		RETURN_FALSE;
	}
	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool memcache_setoptimeout( object memcache , int timeoutms )
   Set the timeout, in milliseconds, for subsequent operations on all open connections */
PHP_FUNCTION(memcache_setoptimeout)
{
	mmc_pool_t *pool;
	mmc_t *mmc;
	int i;
	zval *mmc_object = getThis();
	long timeoutms = 0;

	if (mmc_object == NULL) {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Ol", &mmc_object, memcache_class_entry_ptr, &timeoutms) == FAILURE) {
			return;
		}
	}
	else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &timeoutms) == FAILURE) {
			return;
		}
	}

	if (timeoutms < 1) {
		timeoutms = MEMCACHE_G(default_timeout_ms);
	}

	if (!mmc_get_pool(mmc_object, &pool TSRMLS_CC)) {
		RETURN_FALSE;
	}

	for (i = 0; i < pool->num_servers; i++) {
		mmc = pool->servers[i];
		mmc->timeoutms = timeoutms;
	}
	RETURN_TRUE;
}
/* }}} */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
