<?php

namespace Elastica\Aggregation;

/**
 * Class AbstractTermsAggregation.
 */
abstract class AbstractTermsAggregation extends AbstractSimpleAggregation
{
    use Traits\ShardSizeTrait;

    public const EXECUTION_HINT_MAP = 'map';
    public const EXECUTION_HINT_GLOBAL_ORDINALS = 'global_ordinals';

    /**
     * Set the minimum number of documents in which a term must appear in order to be returned in a bucket.
     *
     * @return $this
     */
    public function setMinimumDocumentCount(int $count): self
    {
        return $this->setParam('min_doc_count', $count);
    }

    /**
     * Filter documents to include based on a regular expression.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/regexp-syntax.html for syntax
     *
     * @param string $pattern a regular expression, following the Regexp syntax
     *
     * @return $this
     */
    public function setInclude(string $pattern): self
    {
        return $this->setParam('include', $pattern);
    }

    /**
     * Filter documents to include based on a list of exact values.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html#_filtering_values_with_exact_values_2
     *
     * @param string[] $values
     *
     * @return $this
     */
    public function setIncludeAsExactMatch(array $values): self
    {
        return $this->setParam('include', $values);
    }

    /**
     * Set the aggregation filter to use partitions.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html#_filtering_values_with_partitions
     *
     * @return $this
     */
    public function setIncludeWithPartitions(int $partition, int $numPartitions): self
    {
        return $this->setParam('include', [
            'partition' => $partition,
            'num_partitions' => $numPartitions,
        ]);
    }

    /**
     * Filter documents to exclude based on a regular expression.
     *
     * @param string $pattern a regular expression
     *
     * @return $this
     */
    public function setExclude(string $pattern): self
    {
        return $this->setParam('exclude', $pattern);
    }

    /**
     * Filter documents to exclude based on a list of exact values.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html#_filtering_values_with_exact_values_2
     *
     * @param string[] $values
     *
     * @return $this
     */
    public function setExcludeAsExactMatch(array $values): self
    {
        return $this->setParam('exclude', $values);
    }

    /**
     * Sets the amount of terms to be returned.
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * Instruct Elasticsearch to use direct field data or ordinals of the field values to execute this aggregation.
     * The execution hint will be ignored if it is not applicable.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html#search-aggregations-bucket-terms-aggregation-execution-hint
     *
     * @param string $hint Execution hint, use one of self::EXECUTION_HINT_MAP or self::EXECUTION_HINT_GLOBAL_ORDINALS
     *
     * @return $this
     */
    public function setExecutionHint(string $hint): self
    {
        return $this->setParam('execution_hint', $hint);
    }
}
