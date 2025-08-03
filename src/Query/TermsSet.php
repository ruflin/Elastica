<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Exception\InvalidException;
use Elastica\Script\AbstractScript;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-set-query.html
 */
class TermsSet extends AbstractQuery
{
    private string $field;

    /**
     * @param array<bool|float|int|string> $terms
     * @param AbstractScript|string        $minimumShouldMatch
     */
    public function __construct(string $field, array $terms, $minimumShouldMatch)
    {
        if ('' === $field) {
            throw new InvalidException('TermsSet field name has to be set');
        }

        $this->field = $field;
        $this->setTerms($terms);

        if (\is_string($minimumShouldMatch)) {
            $this->setMinimumShouldMatchField($minimumShouldMatch);
        } elseif ($minimumShouldMatch instanceof AbstractScript) {
            $this->setMinimumShouldMatchScript($minimumShouldMatch);
        } else {
            throw new \TypeError(\sprintf('Argument 3 passed to "%s()" must be of type %s|string, %s given.', __METHOD__, AbstractScript::class, \is_object($minimumShouldMatch) ? $minimumShouldMatch::class : \gettype($minimumShouldMatch)));
        }
    }

    /**
     * @param array<bool|float|int|string> $terms
     */
    public function setTerms(array $terms): self
    {
        return $this->addParam($this->field, $terms, 'terms');
    }

    public function setMinimumShouldMatchField(string $minimumShouldMatchField): self
    {
        return $this->addParam($this->field, $minimumShouldMatchField, 'minimum_should_match_field');
    }

    public function setMinimumShouldMatchScript(AbstractScript $script): self
    {
        return $this->addParam($this->field, $script->toArray()['script'], 'minimum_should_match_script');
    }
}
