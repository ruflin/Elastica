<?php
namespace Elastica\Script;

use Elastica\Exception\InvalidException;
use Elastica\Param;

/**
 * Container for scripts as fields.
 *
 * @author Sebastien Lavoie <github@lavoie.sl>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-script-fields.html
 */
class ScriptFields extends Param
{
    /**
     * @param \Elastica\Script\Script[]|array $scripts OPTIONAL
     */
    public function __construct(array $scripts = [])
    {
        if ($scripts) {
            $this->setScripts($scripts);
        }
    }

    /**
     * @param string                          $name   Name of the Script field
     * @param \Elastica\Script\AbstractScript $script
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addScript($name, AbstractScript $script)
    {
        if (!is_string($name) || !strlen($name)) {
            throw new InvalidException('The name of a Script is required and must be a string');
        }
        $this->setParam($name, $script);

        return $this;
    }

    /**
     * @param \Elastica\Script\Script[]|array $scripts Associative array of string => Elastica\Script\Script
     *
     * @return $this
     */
    public function setScripts(array $scripts)
    {
        $this->_params = [];
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
        return $this->_convertArrayable($this->_params);
    }
}
