<?php

declare(strict_types=1);

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
    private string $field;

    /**
     * @param list<bool|float|int|string> $terms Terms list, leave empty if building a terms-lookup query
     */
    public function __construct(string $field, array $terms = [])
    {
        if ('' === $field) {
            throw new InvalidException('Terms field name has to be set');
        }

        $this->field = $field;
        $this->setTerms($terms);
    }

    /**
     * Sets terms for the query.
     *
     * @param list<bool|float|int|string> $terms
     */
    public function setTerms(array $terms): self
    {
        return $this->setParam($this->field, $terms);
    }

    /**
     * Adds a single term to the list.
     *
     * @param bool|float|int|string $term
     */
    public function addTerm($term): self
    {
        if (!\is_scalar($term)) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a scalar, %s given.', __METHOD__, \is_object($term) ? $term::class : \gettype($term)));
        }

        $terms = $this->getParam($this->field);

        if (isset($terms['index'])) {
            throw new InvalidException('Mixed terms and terms lookup are not allowed.');
        }

        return $this->addParam($this->field, $term);
    }

    public function setTermsLookup(string $index, string $id, string $path): self
    {
        return $this->setParam($this->field, [
            'index' => $index,
            'id' => $id,
            'path' => $path,
        ]);
    }

    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }
}
