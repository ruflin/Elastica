<?php

namespace Elastica\Suggest;

use Elastica\Exception\InvalidException;
use Elastica\NameableInterface;
use Elastica\Param;

/**
 * Class AbstractSuggestion.
 */
abstract class AbstractSuggest extends Param implements NameableInterface
{
    /**
     * @var string the name of this suggestion
     */
    protected $_name;

    public function __construct(string $name, string $field)
    {
        $this->setName($name);
        $this->setField($field);
    }

    /**
     * Suggest text must be set either globally or per suggestion.
     *
     * @return $this
     */
    public function setText(string $text): self
    {
        return $this->_setRawParam('text', $text);
    }

    /**
     * Suggest prefix must be set either globally or per suggestion.
     *
     * @return $this
     */
    public function setPrefix(string $prefix): self
    {
        return $this->_setRawParam('prefix', $prefix);
    }

    /**
     * Suggest regex must be set either globally or per suggestion.
     *
     * @return $this
     */
    public function setRegex(string $regex): self
    {
        return $this->_setRawParam('regex', $regex);
    }

    /**
     * Expects one of the next params: max_determinized_states - defaults to 10000,
     * flags are ALL (default), ANYSTRING, COMPLEMENT, EMPTY, INTERSECTION, INTERVAL, or NONE.
     *
     * @return $this
     */
    public function setRegexOptions(array $value): self
    {
        return $this->setParam('regex', $value);
    }

    /**
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * @param int $size maximum number of suggestions to be retrieved from each shard
     *
     * @return $this
     */
    public function setShardSize(int $size): self
    {
        return $this->setParam('shard_size', $size);
    }

    /**
     * Sets the name of the suggest. It is automatically set by
     * the constructor.
     *
     * @param string $name The name of the suggest
     *
     * @throws InvalidException If name is empty
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        if (empty($name)) {
            throw new InvalidException('Suggest name has to be set');
        }
        $this->_name = $name;

        return $this;
    }

    /**
     * Retrieve the name of this suggestion.
     */
    public function getName(): string
    {
        return $this->_name;
    }
}
