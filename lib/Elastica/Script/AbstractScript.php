<?php

namespace Elastica\Script;

use Elastica\AbstractUpdateAction;

/**
 * Base class for Script object.
 *
 * @author Nicolas Assing <nicolas.assing@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting.html
 */
abstract class AbstractScript extends AbstractUpdateAction
{
    /**
     * @param array|null $params
     * @param string     $id
     */
    public function __construct(array $params = null, $id = null)
    {
        if ($params) {
            $this->setParams($params);
        }

        if ($id) {
            $this->setId($id);
        }
    }
}
