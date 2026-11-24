<?php

declare(strict_types=1);

namespace Elastica\Suggest;

/**
 * Completion suggester.
 *
 * @author Igor Denisenko <im.denisenko@yahoo.com>
 *
 * @see   https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html
 */
class Completion extends AbstractSuggest
{
    /**
     * Set category or geographic query contexts, including boosts and prefix matching.
     *
     * @param array<string, array> $contexts Context names mapped to their query values
     *
     * @return $this
     */
    public function setContexts(array $contexts): self
    {
        return $this->setParam('contexts', $contexts ?: new \stdClass());
    }

    /**
     * Set fuzzy parameter.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html#fuzzy
     *
     * @param array<string, mixed> $fuzzy
     *
     * @return $this
     */
    public function setFuzzy(array $fuzzy): Completion
    {
        return $this->setParam('fuzzy', $fuzzy);
    }
}
