<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Ids Query.
 *
 * @author Lee Parker
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Tim Rupp
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-query.html
 */
class Ids extends AbstractQuery
{
    /**
     * Creates filter object.
     *
     * @param list<int|string> $ids List of ids
     */
    public function __construct(array $ids = [])
    {
        $this->setIds($ids);
    }

    /**
     * Adds one more filter to the and filter.
     *
     * @param string $id Adds id to filter
     *
     * @return $this
     */
    public function addId(string $id): self
    {
        $this->_params['values'][] = $id;

        return $this;
    }

    /**
     * Sets the ids to filter.
     *
     * @param list<int|string>|string $ids List of ids
     *
     * @return $this
     */
    public function setIds($ids): self
    {
        if (\is_array($ids)) {
            if (!\array_is_list($ids)) {
                throw new InvalidException('Ids must be a list. Use array_values() to reindex the ids.');
            }

            $this->_params['values'] = $ids;
        } else {
            $this->_params['values'] = [$ids];
        }

        return $this;
    }

    public function toArray(): array
    {
        return ['ids' => $this->_params];
    }
}
