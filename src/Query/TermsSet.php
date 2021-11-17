<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;
use Elastica\Script\AbstractScript;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-set-query.html
 */
class TermsSet extends AbstractQuery
{
    /**
     * @param array<bool|float|int|string> $terms
     */
    public function __construct(string $field, array $terms)
    {
        if ('' === $field) {
            throw new InvalidException('Terms field name has to be set');
        }

        if (0 === \count($terms)) {
            throw new InvalidException('Unable to build Terms query: terms must contains at least one item');
        }

        $this->setParam($field, ['terms' => $terms]);
    }

    public function setMinimumShouldMatchField(string $minimumShouldMatchField): self
    {
        $params = $this->getParams();
        $field = \array_key_first($params);

        $this->setParam($field, \array_merge($params[$field], ['minimum_should_match_field' => $minimumShouldMatchField]));

        return $this;
    }

    public function setMinimumShouldMatchScript(AbstractScript $script): self
    {
        $params = $this->getParams();
        $field = \array_key_first($params);

        $this->setParam($field, \array_merge($params[$field], ['minimum_should_match_script' => $script->toArray()['script']]));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $params = $this->getParams();
        $field = \array_key_first($params);

        if (!isset($params[$field]['minimum_should_match_field']) && !isset($params[$field]['minimum_should_match_script'])) {
            throw new InvalidException('One minimum should match criteria must be specified');
        }

        return parent::toArray();
    }
}
