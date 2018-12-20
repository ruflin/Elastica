<?php

namespace Elastica\Query;

/**
 * Type Query.
 *
 * @author James Wilson <jwilson556@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-type-query.html
 */
class Type extends AbstractQuery
{
    /**
     * Type name.
     *
     * @var string
     */
    protected $_type;

    /**
     * Construct Type Query.
     *
     * @param string $type Type name
     */
    public function __construct(string $type = null)
    {
        if (null !== $type) {
            $this->setType($type);
        }
    }

    /**
     * Ads a field with arguments to the range query.
     *
     * @param string $typeName Type name
     *
     * @return $this
     */
    public function setType(string $typeName): self
    {
        $this->_type = $typeName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'type' => ['value' => $this->_type],
        ];
    }
}
