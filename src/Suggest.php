<?php

declare(strict_types=1);

namespace Elastica;

use Elastica\Exception\NotImplementedException;
use Elastica\Suggest\AbstractSuggest;

/**
 * Class Suggest.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters.html
 */
class Suggest extends Param
{
    public function __construct(?AbstractSuggest $suggestion = null)
    {
        if (null !== $suggestion) {
            $this->addSuggestion($suggestion);
        }
    }

    /**
     * Set the global text for this suggester.
     */
    public function setGlobalText(string $text): self
    {
        return $this->setParam('text', $text);
    }

    /**
     * Add a suggestion to this suggest clause.
     */
    public function addSuggestion(AbstractSuggest $suggestion): self
    {
        return $this->addParam('suggestion', $suggestion);
    }

    /**
     * @throws NotImplementedException
     */
    public static function create($suggestion): self
    {
        return match (true) {
            $suggestion instanceof self => $suggestion,
            $suggestion instanceof AbstractSuggest => new static($suggestion),
            default => throw new NotImplementedException(),
        };
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['suggestion'])) {
            $suggestion = $array[$baseName]['suggestion'];
            unset($array[$baseName]['suggestion']);

            foreach ($suggestion as $key => $value) {
                $array[$baseName][$key] = $value;
            }
        }

        return $array;
    }
}
