<?php
namespace Elastica\Aggregation;

use Elastica\Filter\AbstractFilter;

/**
 * Class SignificantTerms.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-significantterms-aggregation.html
 */
class SignificantTerms extends AbstractTermsAggregation
{
    /**
     * The default source of statistical information for background term frequencies is the entire index and this scope can
     * be narrowed through the use of a background_filter to focus in on significant terms within a narrower context.
     *
     * @param AbstractFilter $filter
     *
     * @return $this
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-significantterms-aggregation.html#_custom_background_context
     */
    public function setBackgroundFilter(AbstractFilter $filter)
    {
        return $this->setParam('background_filter', $filter);
    }
}
