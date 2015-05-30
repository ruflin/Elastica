<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;
use Elastica\Exception\JSONParseException;
use Elastica\JSON;

/**
 * Query Builder.
 *
 * @author Chris Gedrim <chris@gedr.im>
 *
 * @link http://www.elastic.co/
 * @deprecated This builder is deprecated and will be removed. Use new Elastica\QueryBuilder instead.
 **/
class Builder extends AbstractQuery
{
    /**
     * Query string.
     *
     * @var string
     */
    private $_string = '{';

    /**
     * Factory method.
     *
     * @param string $string JSON encoded string to use as query.
     *
     * @return self
     */
    public static function factory($string = null)
    {
        return new self($string);
    }

    /**
     * Constructor.
     *
     * @param string $string JSON encoded string to use as query.
     */
    public function __construct($string = null)
    {
        if (!$string == null) {
            $this->_string .= substr($string, 1, -1);
        }
    }

    /**
     * Output the query string.
     *
     * @return string
     */
    public function __toString()
    {
        return rtrim($this->_string, ',').'}';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        try {
            return JSON::parse($input = $this->__toString());
        } catch (JSONParseException $e) {
            throw new InvalidException(sprintf(
                'The produced query is not a valid json string : "%s"',
                $input
            ));
        }
    }

    /**
     * Allow wildcards (*, ?) as the first character in a query.
     *
     * @param bool $bool Defaults to true.
     *
     * @return $this
     */
    public function allowLeadingWildcard($bool = true)
    {
        return $this->field('allow_leading_wildcard', (bool) $bool);
    }

    /**
     * Enable best effort analysis of wildcard terms.
     *
     * @param bool $bool Defaults to true.
     *
     * @return $this
     */
    public function analyzeWildcard($bool = true)
    {
        return $this->field('analyze_wildcard', (bool) $bool);
    }

    /**
     * Set the analyzer name used to analyze the query string.
     *
     * @param string $analyzer Analyzer to use.
     *
     * @return $this
     */
    public function analyzer($analyzer)
    {
        return $this->field('analyzer', $analyzer);
    }

    /**
     * Autogenerate phrase queries.
     *
     * @param bool $bool Defaults to true.
     *
     * @return $this
     */
    public function autoGeneratePhraseQueries($bool = true)
    {
        return $this->field('auto_generate_phrase_queries', (bool) $bool);
    }

    /**
     * Bool Query.
     *
     * A query that matches documents matching boolean combinations of other queries.
     *
     * The bool query maps to Lucene BooleanQuery.
     *
     * It is built using one or more boolean clauses, each clause with a typed
     * occurrence.
     *
     * The occurrence types are: must, should, must_not.
     *
     * @return $this
     */
    public function bool()
    {
        return $this->fieldOpen('bool');
    }

    /**
     * Close a 'bool' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function boolClose()
    {
        return $this->fieldClose();
    }

    /**
     * Sets the boost value of the query.
     *
     * @param float $boost Defaults to 1.0.
     *
     * @return $this
     */
    public function boost($boost = 1.0)
    {
        return $this->field('boost', (float) $boost);
    }

    /**
     * Close a previously opened brace.
     *
     * @return $this
     */
    public function close()
    {
        $this->_string = rtrim($this->_string, ' ,').'},';

        return $this;
    }

    /**
     * Constant Score Query.
     *
     * A query that wraps a filter or another query and simply returns a constant
     * score equal to the query boost for every document in the filter.
     *
     * Maps to Lucene ConstantScoreQuery.
     *
     * @return $this
     */
    public function constantScore()
    {
        return $this->fieldOpen('constant_score');
    }

    /**
     * Close a 'constant_score' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function constantScoreClose()
    {
        return $this->fieldClose();
    }

    /**
     * The default field for query terms if no prefix field is specified.
     *
     * @param string $field Defaults to _all.
     *
     * @return $this
     */
    public function defaultField($field = '_all')
    {
        return $this->field('default_field', $field);
    }

    /**
     * The default operator used if no explicit operator is specified.
     *
     * For example, with a default operator of OR, the query "capital of Hungary"
     * is translated to "capital OR of OR Hungary", and with default operator of
     * AND, the same query is translated to "capital AND of AND Hungary".
     *
     * @param string $operator Defaults to OR.
     *
     * @return $this
     */
    public function defaultOperator($operator = 'OR')
    {
        return $this->field('default_operator', $operator);
    }

    /**
     * Dis Max Query.
     *
     * A query that generates the union of documents produced by its subqueries,
     * and that scores each document with the maximum score for that document as
     * produced by any subquery, plus a tie breaking increment for any additional
     * matching subqueries.
     *
     * @return $this
     */
    public function disMax()
    {
        return $this->fieldOpen('dis_max');
    }

    /**
     * Close a 'dis_max' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function disMaxClose()
    {
        return $this->fieldClose();
    }

    /**
     * Enable position increments in result queries.
     *
     * @param bool $bool Defaults to true.
     *
     * @return $this
     */
    public function enablePositionIncrements($bool = true)
    {
        return $this->field('enable_position_increments', (bool) $bool);
    }

    /**
     * Enables explanation for each hit on how its score was computed.
     *
     * @param bool $value Turn on / off explain.
     *
     * @return $this
     */
    public function explain($value = true)
    {
        return $this->field('explain', $value);
    }

    /**
     * Open 'facets' block.
     *
     * Facets provide aggregated data based on a search query.
     *
     * In the simple case, a facet can return facet counts for various facet
     * values for a specific field.
     *
     * Elasticsearch supports more advanced facet implementations, such as
     * statistical or date histogram facets.
     *
     * @return $this
     */
    public function facets()
    {
        return $this->fieldOpen('facets');
    }

    /**
     * Close a facets block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function facetsClose()
    {
        return $this->close();
    }

    /**
     * Add a specific field / value entry.
     *
     * @param string $name  Field to add.
     * @param mixed  $value Value to set.
     *
     * @return $this
     */
    public function field($name, $value)
    {
        if (is_bool($value)) {
            $value = '"'.var_export($value, true).'"';
        } elseif (is_array($value)) {
            $value = '["'.implode('","', $value).'"]';
        } else {
            $value = '"'.$value.'"';
        }

        $this->_string .= '"'.$name.'":'.$value.',';

        return $this;
    }

    /**
     * Close a field block.
     *
     * Alias of close() for ease of reading in source.
     * Passed parameters will be ignored, however they can be useful in source for
     * seeing which field is being closed.
     *
     * Builder::factory()
     *     ->query()
     *     ->range()
     *     ->fieldOpen('created')
     *     ->gte('2011-07-18 00:00:00')
     *     ->lt('2011-07-19 00:00:00')
     *     ->fieldClose('created')
     *     ->rangeClose()
     *     ->queryClose();
     *
     * @return $this
     */
    public function fieldClose()
    {
        return $this->close();
    }

    /**
     * Open a node for the specified name.
     *
     * @param string $name Field name.
     *
     * @return $this
     */
    public function fieldOpen($name)
    {
        $this->_string .= '"'.$name.'":';
        $this->open();

        return $this;
    }

    /**
     * Explicitly define fields to return.
     *
     * @param array $fields Array of fields to return.
     *
     * @return $this
     */
    public function fields(array $fields)
    {
        $this->_string .= '"fields":[';

        foreach ($fields as $field) {
            $this->_string .= '"'.$field.'",';
        }

        $this->_string = rtrim($this->_string, ',').'],';

        return $this;
    }

    /**
     * Open a 'filter' block.
     *
     * @return $this
     */
    public function filter()
    {
        return $this->fieldOpen('filter');
    }

    /**
     * Close a filter block.
     *
     * @return $this
     */
    public function filterClose()
    {
        return $this->close();
    }

    /**
     *  Query.
     *
     * @return $this
     */
    public function filteredQuery()
    {
        return $this->fieldOpen('filtered');
    }

    /**
     * Close a 'filtered_query' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function filteredQueryClose()
    {
        return $this->fieldClose();
    }

    /**
     * Set the from parameter (offset).
     *
     * @param int $value Result number to start from.
     *
     * @return $this
     */
    public function from($value = 0)
    {
        return $this->field('from', $value);
    }

    /**
     * Set the minimum similarity for fuzzy queries.
     *
     * @param float $value Defaults to 0.5.
     *
     * @return $this
     */
    public function fuzzyMinSim($value = 0.5)
    {
        return $this->field('fuzzy_min_sim', (float) $value);
    }

    /**
     * Set the prefix length for fuzzy queries.
     *
     * @param int $value Defaults to 0.
     *
     * @return $this
     */
    public function fuzzyPrefixLength($value = 0)
    {
        return $this->field('fuzzy_prefix_length', (int) $value);
    }

    /**
     * Add a greater than (gt) clause.
     *
     * Used in range blocks.
     *
     * @param mixed $value Value to be gt.
     *
     * @return $this
     */
    public function gt($value)
    {
        return $this->field('gt', $value);
    }

    /**
     * Add a greater than or equal to (gte) clause.
     *
     * Used in range blocks.
     *
     * @param mixed $value Value to be gte to.
     *
     * @return $this
     */
    public function gte($value)
    {
        return $this->field('gte', $value);
    }

    /**
     * Automatically lower-case terms of wildcard, prefix, fuzzy, and range queries.
     *
     * @param bool $bool Defaults to true.
     *
     * @return $this
     */
    public function lowercaseExpandedTerms($bool = true)
    {
        return $this->field('lowercase_expanded_terms', (bool) $bool);
    }

    /**
     * Add a less than (lt) clause.
     *
     * Used in range blocks.
     *
     * @param mixed $value Value to be lt.
     *
     * @return $this
     */
    public function lt($value)
    {
        return $this->field('lt', $value);
    }

    /**
     * Add a less than or equal to (lte) clause.
     *
     * Used in range blocks.
     *
     * @param mixed $value Value to be lte to.
     *
     * @return $this
     */
    public function lte($value)
    {
        return $this->field('lte', $value);
    }

    /**
     * Match All Query.
     *
     * A query that matches all documents.
     *
     * Maps to Lucene MatchAllDocsQuery.
     *
     * @param float $boost Boost to use.
     *
     * @return $this
     */
    public function matchAll($boost = null)
    {
        $this->fieldOpen('match_all');

        if (!$boost == null && is_numeric($boost)) {
            $this->field('boost', (float) $boost);
        }

        return $this->close();
    }

    /**
     * The minimum number of should clauses to match.
     *
     * @param int $minimum Minimum number that should match.
     *
     * @return $this
     */
    public function minimumNumberShouldMatch($minimum)
    {
        return $this->field('minimum_number_should_match', (int) $minimum);
    }

    /**
     * The clause (query) must appear in matching documents.
     *
     * @return $this
     */
    public function must()
    {
        return $this->fieldOpen('must');
    }

    /**
     * Close a 'must' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function mustClose()
    {
        return $this->fieldClose();
    }

    /**
     * The clause (query) must not appear in the matching documents.
     *
     * Note that it is not possible to search on documents that only consists of
     * a must_not clauses.
     *
     * @return $this
     */
    public function mustNot()
    {
        return $this->fieldOpen('must_not');
    }

    /**
     * Close a 'must_not' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function mustNotClose()
    {
        return $this->fieldClose();
    }

    /**
     * Add an opening brace.
     *
     * @return $this
     */
    public function open()
    {
        $this->_string .= '{';

        return $this;
    }

    /**
     * Sets the default slop for phrases.
     *
     * If zero, then exact phrase matches are required.
     *
     * @param int $value Defaults to 0.
     *
     * @return $this
     */
    public function phraseSlop($value = 0)
    {
        return $this->field('phrase_slop', (int) $value);
    }

    /**
     *  Query.
     *
     * @return $this
     */
    public function prefix()
    {
        return $this->fieldOpen('prefix');
    }

    /**
     * Close a 'prefix' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function prefixClose()
    {
        return $this->fieldClose();
    }

    /**
     * Queries to run within a dis_max query.
     *
     * @param array $queries Array of queries.
     *
     * @return $this
     */
    public function queries(array $queries)
    {
        $this->_string .= '"queries":[';

        foreach ($queries as $query) {
            $this->_string .= $query.',';
        }

        $this->_string = rtrim($this->_string, ' ,').'],';

        return $this;
    }

    /**
     * Open a query block.
     *
     * @return $this
     */
    public function query()
    {
        return $this->fieldOpen('query');
    }

    /**
     * Close a query block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function queryClose()
    {
        return $this->close();
    }

    /**
     * Query String Query.
     *
     * A query that uses a query parser in order to parse its content
     *
     * @return $this
     */
    public function queryString()
    {
        return $this->fieldOpen('query_string');
    }

    /**
     * Close a 'query_string' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function queryStringClose()
    {
        return $this->fieldClose();
    }

    /**
     * Open a range block.
     *
     * @return $this
     */
    public function range()
    {
        return $this->fieldOpen('range');
    }

    /**
     * Close a range block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function rangeClose()
    {
        return $this->close();
    }

    /**
     * The clause (query) should appear in the matching document.
     *
     * A boolean query with no must clauses, one or more should clauses must
     * match a document.
     *
     * @return $this
     */
    public function should()
    {
        return $this->fieldOpen('should');
    }

    /**
     * Close a 'should' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function shouldClose()
    {
        return $this->fieldClose();
    }

    /**
     * Set the size parameter (number of records to return).
     *
     * @param int $value Number of records to return.
     *
     * @return $this
     */
    public function size($value = 10)
    {
        return $this->field('size', $value);
    }

    /**
     * Allows to add one or more sort on specific fields.
     *
     * @return $this
     */
    public function sort()
    {
        return $this->fieldOpen('sort');
    }

    /**
     * Close a sort block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function sortClose()
    {
        return $this->close();
    }

    /**
     * Add a field to sort on.
     *
     * @param string $name    Field to sort.
     * @param bool   $reverse Reverse direction.
     *
     * @return $this
     */
    public function sortField($name, $reverse = false)
    {
        return $this
            ->fieldOpen('sort')
            ->fieldOpen($name)
            ->field('reverse', $reverse)
            ->close()
            ->close();
    }

    /**
     * Sort on multiple fields.
     *
     * @param array $fields Associative array where the keys are field names to sort on, and the
     *                      values are the sort order: "asc" or "desc"
     *
     * @return $this
     */
    public function sortFields(array $fields)
    {
        $this->_string .= '"sort":[';

        foreach ($fields as $fieldName => $order) {
            $this->_string .= '{"'.$fieldName.'":"'.$order.'"},';
        }

        $this->_string = rtrim($this->_string, ',').'],';

        return $this;
    }

    /**
     * Term Query.
     *
     * Matches documents that have fields that contain a term (not analyzed).
     *
     * The term query maps to Lucene TermQuery.
     *
     * @return $this
     */
    public function term()
    {
        return $this->fieldOpen('term');
    }

    /**
     * Close a 'term' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function termClose()
    {
        return $this->fieldClose();
    }

    /**
     * Open a 'text_phrase' block.
     *
     * @return $this
     */
    public function textPhrase()
    {
        return $this->fieldOpen('text_phrase');
    }

    /**
     * Close a 'text_phrase' block.
     *
     * @return $this
     */
    public function textPhraseClose()
    {
        return $this->close();
    }

    /**
     * When using dis_max, the disjunction max tie breaker.
     *
     * @param float $multiplier Multiplier to use.
     *
     * @return $this
     */
    public function tieBreakerMultiplier($multiplier)
    {
        return $this->field('tie_breaker_multiplier', (float) $multiplier);
    }

    /**
     *  Query.
     *
     * @return $this
     */
    public function wildcard()
    {
        return $this->fieldOpen('wildcard');
    }

    /**
     * Close a 'wildcard' block.
     *
     * Alias of close() for ease of reading in source.
     *
     * @return $this
     */
    public function wildcardClose()
    {
        return $this->fieldClose();
    }
}
