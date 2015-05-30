<?php
namespace Elastica\Rescore;

use Elastica\Param;

/**
 * Abstract rescore object. Should be extended by all rescorers.
 *
 * @author Jason Hu <mjhu91@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-rescore.html
 */
abstract class AbstractRescore extends Param
{
    /**
     * Overridden to return rescore as name.
     *
     * @return string name
     */
    protected function _getBaseName()
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
    public function setWindowSize($size)
    {
        return $this->setParam('window_size', $size);
    }
}
