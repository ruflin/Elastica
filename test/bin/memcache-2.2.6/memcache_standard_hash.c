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

/* $Id: memcache_standard_hash.c 303962 2010-10-03 15:48:23Z hradtke $ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_memcache.h"

ZEND_EXTERN_MODULE_GLOBALS(memcache)

typedef struct mmc_standard_state {
	int						num_servers;
	mmc_t					**buckets;
	int						num_buckets;
	mmc_hash_function		hash;
} mmc_standard_state_t;

void *mmc_standard_create_state(mmc_hash_function hash) /* {{{ */
{
	mmc_standard_state_t *state = emalloc(sizeof(mmc_standard_state_t));
	memset(state, 0, sizeof(mmc_standard_state_t));
	state->hash = hash;
	return state;
}
/* }}} */

void mmc_standard_free_state(void *s) /* {{{ */
{
	mmc_standard_state_t *state = s;
	if (state != NULL) {
		if (state->buckets != NULL) {
			efree(state->buckets);
		}
		efree(state);
	}
}
/* }}} */

static unsigned int mmc_hash(mmc_standard_state_t *state, const char *key, int key_len) /* {{{ */
{
	unsigned int hash = (state->hash(key, key_len) >> 16) & 0x7fff;
  	return hash ? hash : 1;
}
/* }}} */

mmc_t *mmc_standard_find_server(void *s, const char *key, int key_len TSRMLS_DC) /* {{{ */
{
	mmc_standard_state_t *state = s;
	mmc_t *mmc;

	if (state->num_servers > 1) {
		unsigned int hash = mmc_hash(state, key, key_len), i;
		mmc = state->buckets[hash % state->num_buckets];

		/* perform failover if needed */
		for (i=0; !mmc_open(mmc, 0, NULL, NULL TSRMLS_CC) && MEMCACHE_G(allow_failover) && i<MEMCACHE_G(max_failover_attempts); i++) {
			char *next_key = emalloc(key_len + MAX_LENGTH_OF_LONG + 1);
			int next_len = sprintf(next_key, "%d%s", i+1, key);
			MMC_DEBUG(("mmc_standard_find_server: failed to connect to server '%s:%d' status %d, trying next", mmc->host, mmc->port, mmc->status));

			hash += mmc_hash(state, next_key, next_len);
			mmc = state->buckets[hash % state->num_buckets];

			efree(next_key);
		}
	}
	else {
		mmc = state->buckets[0];
		mmc_open(mmc, 0, NULL, NULL TSRMLS_CC);
	}

	return mmc->status != MMC_STATUS_FAILED ? mmc : NULL;
}
/* }}} */

void mmc_standard_add_server(void *s, mmc_t *mmc, unsigned int weight) /* {{{ */
{
	mmc_standard_state_t *state = s;
	int i;

	/* add weight number of buckets for this server */
	if (state->num_buckets) {
		state->buckets = erealloc(state->buckets, sizeof(mmc_t *) * (state->num_buckets + weight));
	}
	else {
		state->buckets = emalloc(sizeof(mmc_t *) * (weight));
	}

	for (i=0; i<weight; i++) {
		state->buckets[state->num_buckets + i] = mmc;
	}

	state->num_buckets += weight;
	state->num_servers++;
}
/* }}} */

mmc_hash_t mmc_standard_hash = {
	mmc_standard_create_state,
	mmc_standard_free_state,
	mmc_standard_find_server,
	mmc_standard_add_server
};

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
