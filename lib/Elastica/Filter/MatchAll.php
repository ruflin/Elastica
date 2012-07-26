<?php
/**
 * Match all filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_MatchAll extends Elastica_Filter_Abstract
{
    /**
     * Creates match all filter
     */
    public function __construct()
    {
        $this->_params = new stdClass();
    }
}
