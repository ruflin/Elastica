<?php

namespace Elastica\Script;

use Elastica\Exception\InvalidException;
use Elastica\Param;

/**
 * Container for scripts as fields.
 *
 * @author Sebastien Lavoie <github@lavoie.sl>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-script-fields.html
 */
class ScriptFields extends Param
{
    /**
     * @param array|Script[] $scripts OPTIONAL
     */
    public function __construct(array $scripts = [])
    {
        if ($scripts) {
            $this->setScripts($scripts);
        }
    }

    /**
     * @param string $name Name of the Script field
     *
     * @throws InvalidException
     *
     * @return $this
     */
    public function addScript(string $name, AbstractScript $script): self
    {
        if (!\strlen($name)) {
            throw new InvalidException('The name of a Script is required and must be a string');
        }
        $this->setParam($name, $script);

        return $this;
    }

    /**
     * @param array|Script[] $scripts Associative array of string => Elastica\Script\Script
     *
     * @return $this
     */
    public function setScripts(array $scripts): self
    {
        $this->_params = [];
        foreach ($scripts as $name => $script) {
            $this->addScript($name, $script);
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->_convertArrayable($this->_params);
    }
}
