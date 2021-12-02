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
     * @param array<bool|float|int|string> $terms Terms list, leave empty if building a terms-lookup query
     */
    public function __construct(string $field, array $terms = [])
    {
        if ('' === $field) {
            throw new InvalidException('Terms field name has to be set');
        }

        $this->setParam($field, $terms);
    }

    /**
     * Sets terms for the query.
     *
     * @param array<bool|float|int|string> $terms
     */
    public function setTerms(array $terms): self
    {
        if (null === $field = \array_key_first($this->getParams())) {
            throw new InvalidException('No field has been set.');
        }

        return $this->setParam($field, $terms);
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

        if (null === $field = \array_key_first($params = $this->getParams())) {
            throw new InvalidException('No field has been set.');
        }

        if (isset($params[$field]['index'])) {
            throw new InvalidException('Mixed terms and terms lookup are not allowed.');
        }

        return $this->addParam($field, $term);
    }

    public function setTermsLookup(string $index, string $id, string $path): self
    {
        if (null === $field = \array_key_first($this->getParams())) {
            throw new InvalidException('No field has been set.');
        }

        return $this->setParam($field, [
            'index' => $index,
            'id' => $id,
            'path' => $path,
        ]);
    }
}
