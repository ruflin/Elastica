<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Terms query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Roberto Nygaard <roberto@nygaard.es>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html
 */
class Terms extends AbstractQuery
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var array<float|int|string>
     */
    private $terms;

    /**
     * @var string[]|null
     */
    private $lookup;

    /**
     * @param array<bool|float|int|string> $terms Terms list, leave empty if building a terms-lookup query
     */
    public function __construct(string $field, array $terms = [])
    {
        if (empty($field)) {
            throw new InvalidException('Terms field name has to be set');
        }

        $this->field = $field;
        $this->terms = $terms;
    }

    /**
     * Sets terms for the query.
     *
     * @param array<bool|float|int|string> $terms
     */
    public function setTerms(array $terms): self
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * Adds a single term to the list.
     *
     * @param bool|float|int|string $term
     */
    public function addTerm($term): self
    {
        if (!\is_scalar($term)) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a scalar, %s given.', __METHOD__, \is_object($term) ? \get_class($term) : \gettype($term)));
        }

        $this->terms[] = $term;

        return $this;
    }

    public function setTermsLookup(string $index, string $id, string $path): self
    {
        $this->lookup = [
            'index' => $index,
            'id' => $id,
            'path' => $path,
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (null !== $this->lookup && \count($this->terms)) {
            throw new InvalidException('Unable to build Terms query: only one of terms or lookup properties should be set');
        }

        if (null !== $this->lookup) {
            $this->setParam($this->field, $this->lookup);
        } else {
            $this->setParam($this->field, $this->terms);
        }

        return parent::toArray();
    }
}
