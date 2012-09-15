/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2004 The PHP Group                                |
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

/* $Id: memcache_queue.c 303965 2010-10-03 20:40:19Z hradtke $ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "memcache_queue.h"

void mmc_queue_push(mmc_queue_t *queue, void *ptr) {
	if (mmc_queue_contains(queue, ptr)) return;

	if (queue->len >= queue->alloc) {
		int increase = 1 + MMC_QUEUE_PREALLOC;
		queue->alloc += increase;
		queue->items = erealloc(queue->items, sizeof(*queue->items) * queue->alloc);
		
		/* move tail segment downwards */
		if (queue->head < queue->tail) {
			memmove(queue->items + queue->tail + increase, queue->items + queue->tail, (queue->alloc - queue->tail - increase) * sizeof(*queue->items));
			queue->tail += increase;
		}
	}

	if (queue->len) {
		queue->head++;

		if (queue->head >= queue->alloc) {
			queue->head = 0;
		}
	}

	queue->items[queue->head] = ptr;
	queue->len++;
}

void *mmc_queue_pop(mmc_queue_t *queue) {
	if (queue->len) {
		void *ptr;
		
		ptr = queue->items[queue->tail];
		queue->len--;

		if (queue->len) {
			queue->tail++;
	
			if (queue->tail >= queue->alloc) {
				queue->tail = 0;
			}
		}
		
		return ptr;
	}
	return NULL;
}

int mmc_queue_contains(mmc_queue_t *queue, void *ptr) {
	if (queue != NULL) {
		int i;
		
		for (i=0; i < queue->len; i++) {
			if (mmc_queue_item(queue, i) == ptr) {
				return 1;
			}
		}
	}
	
	return 0;
}

void mmc_queue_free(mmc_queue_t *queue) {
	if (queue->items != NULL) {
		efree(queue->items);
	}
	memset(queue, 0, sizeof(*queue));
}

void mmc_queue_copy(mmc_queue_t *source, mmc_queue_t *target) {
	if (target->alloc != source->alloc) {
		target->alloc = source->alloc;
		erealloc(target->items, sizeof(*target->items) * target->alloc);
	}
	
	memcpy(target->items, source->items, sizeof(*source->items) * source->alloc);
	target->head = source->head;
	target->tail = source->tail;
	target->len = source->len;
}

void mmc_queue_remove(mmc_queue_t *queue, void *ptr) { 
	void *item;
	mmc_queue_t original = *queue;
	mmc_queue_release(queue);
	
	while ((item = mmc_queue_pop(&original)) != NULL) {
		if (item != ptr) {
			mmc_queue_push(queue, item);
		}
	}
	
	mmc_queue_free(&original);
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
