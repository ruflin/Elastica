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

/* $Id: php_memcache.h 303967 2010-10-03 20:57:13Z hradtke $ */

#ifndef PHP_MEMCACHE_H
#define PHP_MEMCACHE_H

extern zend_module_entry memcache_module_entry;
#define phpext_memcache_ptr &memcache_module_entry

#ifdef PHP_WIN32
#define PHP_MEMCACHE_API __declspec(dllexport)
#else
#define PHP_MEMCACHE_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

#include "ext/standard/php_smart_str_public.h"

PHP_MINIT_FUNCTION(memcache);
PHP_MSHUTDOWN_FUNCTION(memcache);
PHP_RINIT_FUNCTION(memcache);
PHP_MINFO_FUNCTION(memcache);

PHP_FUNCTION(memcache_connect);
PHP_FUNCTION(memcache_pconnect);
PHP_FUNCTION(memcache_add_server);
PHP_FUNCTION(memcache_set_server_params);
PHP_FUNCTION(memcache_get_server_status);
PHP_FUNCTION(memcache_get_version);
PHP_FUNCTION(memcache_add);
PHP_FUNCTION(memcache_set);
PHP_FUNCTION(memcache_replace);
PHP_FUNCTION(memcache_get);
PHP_FUNCTION(memcache_delete);
PHP_FUNCTION(memcache_debug);
PHP_FUNCTION(memcache_get_stats);
PHP_FUNCTION(memcache_get_extended_stats);
PHP_FUNCTION(memcache_set_compress_threshold);
PHP_FUNCTION(memcache_increment);
PHP_FUNCTION(memcache_decrement);
PHP_FUNCTION(memcache_close);
PHP_FUNCTION(memcache_flush);
PHP_FUNCTION(memcache_setoptimeout);

#define PHP_MEMCACHE_VERSION "2.2.6"

#define MMC_BUF_SIZE 4096
#define MMC_SERIALIZED 1
#define MMC_COMPRESSED 2
#define MMC_DEFAULT_TIMEOUT 1				/* seconds */
#define MMC_KEY_MAX_SIZE 250				/* stoled from memcached sources =) */
#define MMC_DEFAULT_RETRY 15 				/* retry failed server after x seconds */
#define MMC_DEFAULT_SAVINGS 0.2				/* minimum 20% savings for compression to be used */
#define MMC_DEFAULT_CACHEDUMP_LIMIT	100		/* number of entries */

#define MMC_STATUS_FAILED 0
#define MMC_STATUS_DISCONNECTED 1
#define MMC_STATUS_UNKNOWN 2
#define MMC_STATUS_CONNECTED 3

#define MMC_OK 					0
#define MMC_REQUEST_FAILURE 	-1

#define MMC_STANDARD_HASH 1
#define MMC_CONSISTENT_HASH 2
#define MMC_HASH_CRC32 1					/* CRC32 hash function */
#define MMC_HASH_FNV1A 2					/* FNV-1a hash function */

#define MMC_CONSISTENT_POINTS 160			/* points per server */
#define MMC_CONSISTENT_BUCKETS 1024			/* number of precomputed buckets, should be power of 2 */

typedef struct mmc {
	php_stream				*stream;
	char					inbuf[MMC_BUF_SIZE];
	smart_str				outbuf;
	char					*host;
	unsigned short			port;
	long					timeout;
	long					timeoutms; /* takes precedence over timeout */
	long					connect_timeoutms; /* takes precedence over timeout */
	long					failed;
	long					retry_interval;
	int						persistent;
	int						status;
	char					*error;					/* last error message */
	int						errnum;					/* last error code */
	zval					*failure_callback;
	zend_bool				in_free;
} mmc_t;

/* hashing strategy */
typedef unsigned int (*mmc_hash_function)(const char *, int);
typedef void * (*mmc_hash_create_state)(mmc_hash_function);
typedef void (*mmc_hash_free_state)(void *);
typedef mmc_t * (*mmc_hash_find_server)(void *, const char *, int TSRMLS_DC);
typedef void (*mmc_hash_add_server)(void *, mmc_t *, unsigned int);

#define mmc_pool_find(pool, key, key_len) \
	pool->hash->find_server(pool->hash_state, key, key_len)

typedef struct mmc_hash {
	mmc_hash_create_state	create_state;
	mmc_hash_free_state		free_state;
	mmc_hash_find_server	find_server;
	mmc_hash_add_server		add_server;
} mmc_hash_t;

/* 32 bit magic FNV-1a prime and init */
#define FNV_32_PRIME 0x01000193
#define FNV_32_INIT 0x811c9dc5 

typedef struct mmc_pool {
	mmc_t					**servers;
	int						num_servers;
	mmc_t					**requests;
	int						compress_threshold;
	double					min_compress_savings;
	zend_bool				in_free;
	mmc_hash_t				*hash;
	void					*hash_state;
} mmc_pool_t;

/* our globals */
ZEND_BEGIN_MODULE_GLOBALS(memcache)
	long debug_mode;
	long default_port;
	long num_persistent;
	long compression_level;
	long allow_failover;
	long chunk_size;
	long max_failover_attempts;
	long hash_strategy;
	long hash_function;
	long default_timeout_ms;
ZEND_END_MODULE_GLOBALS(memcache)

#if (PHP_MAJOR_VERSION == 5) && (PHP_MINOR_VERSION >= 3)
#   define IS_CALLABLE(cb_zv, flags, cb_sp) zend_is_callable((cb_zv), (flags), (cb_sp) TSRMLS_CC)
#else
#   define IS_CALLABLE(cb_zv, flags, cb_sp) zend_is_callable((cb_zv), (flags), (cb_sp))
#endif

/* internal functions */
mmc_t *mmc_server_new(char *, int, unsigned short, int, int, int TSRMLS_DC);
mmc_t *mmc_find_persistent(char *, int, int, int, int TSRMLS_DC);
int mmc_server_failure(mmc_t * TSRMLS_DC);
void mmc_server_deactivate(mmc_t * TSRMLS_DC);

int mmc_prepare_key(zval *, char *, unsigned int * TSRMLS_DC);
int mmc_prepare_key_ex(const char *, unsigned int, char *, unsigned int * TSRMLS_DC);

mmc_pool_t *mmc_pool_new(TSRMLS_D);
void mmc_pool_free(mmc_pool_t * TSRMLS_DC);
void mmc_pool_add(mmc_pool_t *, mmc_t *, unsigned int);
int mmc_pool_store(mmc_pool_t *, const char *, int, const char *, int, int, int, const char *, int TSRMLS_DC);
int mmc_open(mmc_t *, int, char **, int * TSRMLS_DC);
int mmc_exec_retrieval_cmd(mmc_pool_t *, const char *, int, zval **, zval * TSRMLS_DC);
int mmc_delete(mmc_t *, const char *, int, int TSRMLS_DC);

/* session handler struct */
#if HAVE_MEMCACHE_SESSION
#include "ext/session/php_session.h"

extern ps_module ps_mod_memcache;
#define ps_memcache_ptr &ps_mod_memcache

PS_FUNCS(memcache);
#endif

/* {{{ macros */
#if ZEND_DEBUG

void mmc_debug(const char *format, ...);

#define MMC_DEBUG(info) \
{\
	mmc_debug info; \
}\

#else

#define MMC_DEBUG(info) \
{\
}\

#endif
/* }}} */

#ifdef ZTS
#define MEMCACHE_G(v) TSRMG(memcache_globals_id, zend_memcache_globals *, v)
#else
#define MEMCACHE_G(v) (memcache_globals.v)
#endif

#ifndef ZSTR
#define ZSTR
#endif

#endif	/* PHP_MEMCACHE_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
