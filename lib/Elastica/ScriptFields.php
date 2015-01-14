<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Container for scripts as fields
 *
 * @category Xodoa
 * @package Elastica
 * @author Sebastien Lavoie <github@lavoie.sl>
 * @link http://www.elasticsearch.org/guide/reference/api/search/script-fields.html
 */
class ScriptFields extends Param
{
    /**
     * @param \Elastica\Script[]|array $scripts OPTIONAL
     */
    public function __construct(array $scripts = array())
    {
        if ($scripts) {
            $this->setScripts($scripts);
        }
    }

    /**
     * @param  string                               $name   Name of the Script field
     * @param  \Elastica\Script                     $script
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\ScriptFields
     */
    public function addScript($name, Script $script)
    {
        if (!is_string($name) || !strlen($name)) {
            throw new InvalidException('The name of a Script is required and must be a string');
        }
        $this->setParam($name, $script->toArray());

        return $this;
    }

    /**
     * @param  \Elastica\Script[]|array $scripts Associative array of string => Elastica\Script
     * @return \Elastica\ScriptFields
     */
    public function setScripts(array $scripts)
    {
        $this->_params = array();
        foreach ($scripts as $name => $script) {
            $this->addScript($name, $script);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_params;
    }
}
