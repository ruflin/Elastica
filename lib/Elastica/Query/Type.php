<?php
namespace Elastica\Query;

/**
 * Type Query.
 *
 * @author James Wilson <jwilson556@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-type-query.html
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
    public function __construct($type = null)
    {
        if ($type) {
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
    public function setType($typeName)
    {
        $this->_type = $typeName;

        return $this;
    }

    /**
     * Convert object to array.
     *
     * @see \Elastica\Query\AbstractQuery::toArray()
     *
     * @return array Query array
     */
    public function toArray()
    {
        return [
            'type' => ['value' => $this->_type],
        ];
    }
}
