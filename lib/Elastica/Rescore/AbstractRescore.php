<?php

namespace Elastica\Rescore;

use Elastica\Param;

/**
 * Abstract rescore object. Should be extended by all rescorers.
 *
 * @author Jason Hu <mjhu91@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-rescore.html
 */
abstract class AbstractRescore extends Param
{
    /**
     * Overridden to return rescore as name.
     *
     * @return string name
     */
    protected function _getBaseName(): string
    {
        return 'rescore';
    }

    /**
     * Sets window_size.
     *
     * @param int $size
     *
     * @return $this
     */
    public function setWindowSize(int $size): AbstractRescore
    {
        return $this->setParam('window_size', $size);
    }
}
