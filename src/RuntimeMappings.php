<?php

declare(strict_types=1);

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Runtime field definitions applied only to a search request.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/runtime-search-request.html
 */
class RuntimeMappings extends Param
{
    /**
     * @param array<string, array> $fields Native Elasticsearch runtime field definitions
     */
    public function __construct(array $fields = [])
    {
        $this->setFields($fields);
    }

    /**
     * @param array<string, array> $fields Native Elasticsearch runtime field definitions
     *
     * @return $this
     */
    public function setFields(array $fields): self
    {
        $this->_params = [];
        foreach ($fields as $name => $definition) {
            $this->addField($name, $definition);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addField(string $name, array $definition): self
    {
        if ('' === $name) {
            throw new InvalidException('The name of a runtime field is required');
        }

        return $this->setParam($name, $definition);
    }

    public function toArray(): array
    {
        return $this->_convertArrayable($this->_params);
    }
}
