<?php
/**
 * Container for scripts as fields
 *
 * @category Xodoa
 * @package Elastica
 * @author Sebastien Lavoie <github@lavoie.sl>
 * @link http://www.elasticsearch.org/guide/reference/api/search/script-fields.html
 */
class Elastica_ScriptFields extends Elastica_Param
{
    /**
     * @param array $scripts OPTIONAL
     */
    public function __construct(array $scripts = array())
    {
        if ($scripts) {
            $this->setScripts($scripts);
        }
    }

    /**
     * @param  string                $name   Name of the Script field
     * @param  Elastica_Script       $script
     * @return Elastica_ScriptFields
     */
    public function addScript($name, Elastica_Script $script)
    {
        if (!is_string($name) || !strlen($name)) {
            throw new Elastica_Exception_Invalid('The name of a Script is required and must be a string');
        }
        $this->setParam($name, $script->toArray());

        return $this;
    }

    /**
     * @param  array                 $script Associative array of string => Elastica_Script
     * @return Elastica_ScriptFields
     */
    public function setScripts(array $scripts)
    {
        $this->_params = array();
        foreach ($scripts as $name => $script) {
            $this->addScript($name, $script);
        }

        return $this;
    }

    public function toArray()
    {
        return $this->_params;
    }
}
