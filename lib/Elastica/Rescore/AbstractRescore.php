<?php

namespace Elastica\Rescore;
use Elastica\Param;

/**
 * Abstract rescore object. Should be extended by all rescorers.
 *
 * @category Xodoa
 * @package Elastica
 * @author Jason Hu <mjhu91@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/rescore/
 */
abstract class AbstractRescore extends Param
{
	/**
	 * Overridden to return rescore as name
     *
     * @return string name
     */
    protected function _getBaseName()
    {
        return 'rescore';
    }

    /**
     * Sets window_size
     *
     * @param int $size
     * @return \Elastica\Rescore
     */
    public function setWindowSize($size)
    {
        return $this->setParam('window_size', $size);
    }
}