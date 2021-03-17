# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/ruflin/Elastica/compare/7.1.1...master)
### Backward Compatibility Breaks
### Added
### Changed
### Deprecated
### Removed
### Fixed
### Security

## [7.1.1](https://github.com/ruflin/Elastica/compare/7.1.0...7.1.1)
### Backward Compatibility Breaks
* Changed `Elastica\Query\MatchQuery::setFieldParam()` signature to allow passing bool, float or int [#1941](https://github.com/ruflin/Elastica/pull/1941)
* Changed `Elastica\Query\MatchPhraseQuery::setFieldParam()` signature to allow passing bool, float or int [#1944](https://github.com/ruflin/Elastica/pull/1944)
### Added
* Excluded `docker` directory in `.gitattributes` [#1938](https://github.com/ruflin/Elastica/pull/1938)
### Changed
* Included `Content-Type` HTTP header every time, whatever the content of the body is [#1780](https://github.com/ruflin/Elastica/pull/1780)
* Changed `Elastica\Status::indexExists()`, `Elastica\Status::aliasExists()` and `Elastica\Status::getIndicesWithAlias()` signatures [#1929](https://github.com/ruflin/Elastica/pull/1929)
* Replaced `call_user_func()` and `call_user_func_array()` by direct calls [#1923](https://github.com/ruflin/Elastica/pull/1923)
* Replaced legacy constant `CURLINFO_HTTP_CODE` by `CURLINFO_RESPONSE_CODE` [#1931](https://github.com/ruflin/Elastica/pull/1931)
* Updated `php-cs-fixer` to `2.18.3` [#1915](https://github.com/ruflin/Elastica/pull/1915)
* Updated `composer-normalize` to `2.13.3` [#1927](https://github.com/ruflin/Elastica/pull/1927)
### Deprecated
* Deprecated `Elastica\Transport\HttpAdapter` class [#1940](https://github.com/ruflin/Elastica/pull/1940)
### Fixed
* Fixed wrong `ltrim` usage in guzzle transport [#1783](https://github.com/ruflin/Elastica/pull/1783)
* Fixed `_seq_no` and `_primary_term` wrong initialization [#1920](https://github.com/ruflin/Elastica/pull/1920)
* Fixed `Elastica\Connection\StrategyInterface` instance checks [#1921](https://github.com/ruflin/Elastica/pull/1921)
* Fixed various PHPDoc annotations [#1922](https://github.com/ruflin/Elastica/pull/1922)
* Fixed numeric index names are returned as `int` in `Elastica\Status::getIndexNames()` [#1928](https://github.com/ruflin/Elastica/pull/1928)
* Fixed using raw array in `post_filter` [#1950](https://github.com/ruflin/Elastica/pull/1950)

## [7.1.0](https://github.com/ruflin/Elastica/compare/7.0.0...7.1.0)
### Backward Compatibility Breaks
* Added a default value to `Elastica\Aggregation\Range::setKeyed()` and `Elastica\Aggregation\PercentilesBucket::setKeyed()` [#1876](https://github.com/ruflin/Elastica/pull/1876)
* Removed type-hint to `Elastica\Aggregation\Percentiles::setMissing()` argument [#1875](https://github.com/ruflin/Elastica/pull/1875)
* Allowed the Terms query to accept an array of bool, float, int and/or string [#1872](https://github.com/ruflin/Elastica/pull/1872)
### Added
* Added `auth_type` parameter in the client class config to specify the type of authentication (allowed values are `basic, digest, gssnegotiate, ntlm`) [#1790](https://github.com/ruflin/Elastica/pull/1790)
* Added `if_seq_no` / `if_primary_term` to replace `version` for [optimistic concurrency control](https://www.elastic.co/guide/en/elasticsearch/reference/7.x/optimistic-concurrency-control.html) [#1803](https://github.com/ruflin/Elastica/pull/1803)
* Added `Elastica\Aggregation\PercentilesBucket` aggregation [#1806](https://github.com/ruflin/Elastica/pull/1806)
* Added `weighted_avg` to aggregations DSL [#1814](https://github.com/ruflin/Elastica/pull/1814)
* Added support for defining a connection pool with DSN. Example: `pool(http://127.0.0.1 http://127.0.0.2/bar?timeout=4)` [#1808](https://github.com/ruflin/Elastica/pull/1808)
* Added `Elastica\Aggregation\Composite` aggregation [#1804](https://github.com/ruflin/Elastica/pull/1804)
* Added `symfony/deprecation-contracts` package to handle deprecations [#1823](https://github.com/ruflin/Elastica/pull/1823)
* Added `list_syntax` CS rule [#1854](https://github.com/ruflin/Elastica/pull/1854)
* Added `native_constant_invocation` CS rule [#1833](https://github.com/ruflin/Elastica/pull/1833)
* Added `static_lambda` CS rule [#1870](https://github.com/ruflin/Elastica/pull/1870)
* Added `Elastica\Aggregation\DateRange::setTimezone()` [#1847](https://github.com/ruflin/Elastica/pull/1847)
* Added endpoint options support to `Elastica\Index::create()` [#1859](https://github.com/ruflin/Elastica/pull/1859)
* Added `Elastica\Aggregation\DateHistogram::setKeyed()` [#1876](https://github.com/ruflin/Elastica/pull/1876)
* Added `Elastica\Aggregation\GeoDistance::setKeyed()` [#1876](https://github.com/ruflin/Elastica/pull/1876)
* Added `Elastica\Aggregation\Histogram::setKeyed()` [#1876](https://github.com/ruflin/Elastica/pull/1876)
* Added `Elastica\Aggregation\IpRange::setKeyed()` [#1876](https://github.com/ruflin/Elastica/pull/1876)
* Added `Elastica\Aggregation\GeotileGridAggregation` [#1880](https://github.com/ruflin/Elastica/pull/1880)
* Added `Elastica\Aggregation\Avg::setMissing()`, `Elastica\Aggregation\Cardinality::setMissing()`, `Elastica\Aggregation\DateRange::setMissing()`, `Elastica\Aggregation\DateHistogram::setMissing()`, `Elastica\Aggregation\ExtendedStats::setMissing()`, `Elastica\Aggregation\Histogram::setMissing()`, `Elastica\Aggregation\Max::setMissing()`, `Elastica\Aggregation\Min::setMissing()`, `Elastica\Aggregation\Stats::setMissing()`, `Elastica\Aggregation\Sum::setMissing()`, `Elastica\Aggregation\Terms::setMissing()` [#1876](https://github.com/ruflin/Elastica/pull/1876)
* Supported `guzzlehttp/guzzle` 7.x [#1816](https://github.com/ruflin/Elastica/pull/1816)
* Supported PHP 8.0 [#1794](https://github.com/ruflin/Elastica/pull/1794)
* Supported BC break on `elasticsearch/elasticsearch` version `7.4.0` [#1864](https://github.com/ruflin/Elastica/pull/1864)
### Changed
* Allowed `string` such as `wait_for` to be passed to `AbstractUpdateAction::setRefresh` [#1791](https://github.com/ruflin/Elastica/pull/1791)
* Allowed float values for connection timeout and connection connect-timeout, providing ms precision for those. Previous precision was second. [#1868](https://github.com/ruflin/Elastica/pull/1868)
* Changed the return type of `AbstractUpdateAction::getRefresh` to `boolean|string` [#1791](https://github.com/ruflin/Elastica/pull/1791)
* Reviewed options handling in `Elastica\Index::create()` [#1822](https://github.com/ruflin/Elastica/pull/1822)
* Replaced deprecated `exceptions` request option by `http_errors` request option in Guzzle transport [#1817](https://github.com/ruflin/Elastica/pull/1817)
* Run coding styles check on github action [#1878](https://github.com/ruflin/Elastica/pull/1878)
* Run unit tests on github action [#1882](https://github.com/ruflin/Elastica/pull/1882)
* Run functional tests on github action [#1885](https://github.com/ruflin/Elastica/pull/1885)
* Updated `php-cs-fixer` to `2.16.4` [#1830](https://github.com/ruflin/Elastica/pull/1830)
* Updated `php-cs-fixer` to `2.16.7` [#1881](https://github.com/ruflin/Elastica/pull/1881)
* Updated `php-cs-fixer` to `2.17.3` [#1895](https://github.com/ruflin/Elastica/pull/1895)
* Updated `php-cs-fixer` to `2.18.2` [#1897](https://github.com/ruflin/Elastica/pull/1897)
* Used `GuzzleHttp\RequestOptions` constants for configuring request options [#1820](https://github.com/ruflin/Elastica/pull/1820)
* Used new alias endpoints classes [#1839](https://github.com/ruflin/Elastica/pull/1839)
* Used new cache endpoints classes [#1840](https://github.com/ruflin/Elastica/pull/1840)
* Used new ingest pipeline endpoints classes [#1834](https://github.com/ruflin/Elastica/pull/1834)
* Used new mapping endpoints classes [#1845](https://github.com/ruflin/Elastica/pull/1845)
* Used new nodes endpoints classes [#1863](https://github.com/ruflin/Elastica/pull/1863)
* Used new settings endpoints classes [#1852](https://github.com/ruflin/Elastica/pull/1852)
### Deprecated
* Deprecated `Elastica\Aggregation\Range::setKeyedResponse()`, use `setKeyed()` instead [#1848](https://github.com/ruflin/Elastica/pull/1848)
* Deprecated `Elastica\Exception\ResponseException::getElasticsearchException()`, use `getResponse()::getFullError()` instead [#1829](https://github.com/ruflin/Elastica/pull/1829)
* Deprecated `Elastica\QueryBuilder\DSL\Aggregation::global_agg()`, use `global()` instead [#1826](https://github.com/ruflin/Elastica/pull/1826)
* Deprecated `Elastica\Util::getParamName()` [#1832](https://github.com/ruflin/Elastica/pull/1832)
* Deprecated all Processor class names in favor of suffixed class names [#1893](https://github.com/ruflin/Elastica/pull/1893)
* Deprecated Match query class and introduced MatchQuery instead for PHP 8.0 compatibility reason [#1799](https://github.com/ruflin/Elastica/pull/1799)
* Deprecated `version`/`version_type` options [(deprecated in `6.7.0`)](https://www.elastic.co/guide/en/elasticsearch/reference/6.8/docs-update.html) and added `if_seq_no` / `if_primary_term` that replaced it
* Deprecated passing `bool` or `null` as 2nd argument to `Elastica\Index::create()` [#1828](https://github.com/ruflin/Elastica/pull/1828)
### Removed
* Removed HHVM proxy detection [#1818](https://github.com/ruflin/Elastica/pull/1818)
### Fixed
* Fixed issue [1789](https://github.com/ruflin/Elastica/issues/1789)
* Fixed type-hint for `Elastica\QueryBuilder\DSL\Aggregation::sampler()` not consistent with the underlying constructor call [#1815](https://github.com/ruflin/Elastica/pull/1815)
* Fixed `Elastica\Util::toSnakeCase()` with first letter being lower cased [#1831](https://github.com/ruflin/Elastica/pull/1831)
* Fixed handling precision as string in `Elastica\Aggregation\GeohashGrid::setPrecision()` [#1884](https://github.com/ruflin/Elastica/pull/1884)
* Fixed calling `Elastica\Aggregation\Composite::addAfter()` with the `null` value [1877](https://github.com/ruflin/Elastica/pull/1877)
* Replaced `_routing` and `_retry_on_conflict` by `routing` and `retry_on_conflict` in `AbstractUpdateAction` [#1807](https://github.com/ruflin/Elastica/issues/1807)
### Security


## [7.0.0](https://github.com/ruflin/Elastica/compare/7.0.0-beta.4...7.0.0)
### Added
* Added `Elastica\Aggregation\WeightedAvg` aggregation [#1770](https://github.com/ruflin/Elastica/pull/1770)

### Changed
* Added missing Response information to Bulk/ResponseSet [#1776](https://github.com/ruflin/Elastica/pull/1776)


## [7.0.0-beta.4](https://github.com/ruflin/Elastica/compare/7.0.0-beta3...7.0.0-beta.4)
### Backward Compatibility Breaks
* If you're **NOT** using composer to manage your libraries: the root directory of the library's source code moved from `lib/Elastica/` to `src/`.
* The `Wildcard::setValue()` changed its signature: use it to set the value of the wildcard query only.
* The `Wildcard` Query's constructor now requires the `name` and `value` properties.
* The `Terms` Query's constructor now requires the `field` and `terms` properties.

### Added
* Added `AbstractTermsAggregation::setIncludeAsExactMatch()` [#1766](https://github.com/ruflin/Elastica/pull/1766)
* Added `AbstractTermsAggregation::setExcludeAsExactMatch()` [#1766](https://github.com/ruflin/Elastica/pull/1766)
* Added `AbstractTermsAggregation::setIncludeWithPartitions()` [#1766](https://github.com/ruflin/Elastica/pull/1766)
* Added `Elastica\Reindex->setPipeline(Elastica\Pipeline $pipeline): void`. The link between the reindex and the pipeline is solved when `run()` is called, and thus the pipeline given doesn't need to be created before calling `setPipeline()` [#1752](https://github.com/ruflin/Elastica/pull/1752)
* Added `Elastica\Reindex->setRefresh(string $value): void`. It accepts `REFRESH_*` constants from its class [#1752](https://github.com/ruflin/Elastica/pull/1752) and [#1758](https://github.com/ruflin/Elastica/pull/1758)
* Added `Elastica\Reindex->setQuery(Elastica\Query\AbstractQuery $query): void` [#1752](https://github.com/ruflin/Elastica/pull/1752)
* Added constants `PIPELINE`, `REFRESH_TRUE`, `REFRESH_FALSE`, `REFRESH_WAIT_FOR`, `SLICES` and `SLICES_AUTO` to `Elastica\Reindex` [#1752](https://github.com/ruflin/Elastica/pull/1752)
* Added `Elastica\Pipeline->getId(): ?string` [#1752](https://github.com/ruflin/Elastica/pull/1752)
* Added `Elastica\Aggregation\ExtendedStatsBucket` aggregation [#1756](https://github.com/ruflin/Elastica/pull/1756)

### Changed
* Changed `Terms::setTerms()` signature: it now accepts a list of strings only [#1765](https://github.com/ruflin/Elastica/pull/1765)
* Changed `Terms::setTermsLookup()` signature: `index`, `path` and `id` are now required arguments [#1765](https://github.com/ruflin/Elastica/pull/1765)
* Changed `Wildcard::setValue()` and constructor's signature: added more specific `Wildcard::setBoost()` and `Wildcard::setRewrite` methods
* Updated PHP coding standards to adhere to PSR-12 [#1760](https://github.com/ruflin/Elastica/pull/1760)
* Updated to PHPUnit v8.5 [#1759](https://github.com/ruflin/Elastica/pull/1759)
* Refactored code structure: use `src/` and `tests/` folders [#1755](https://github.com/ruflin/Elastica/pull/1755)
* Require elastica-php library >= v7.1.1, fixes an issue on Ingestion/Put() type-hinting
* Require guzzle >= v6.3 as development library: fixes issues on PHP >= 7.2
* Require phpunit >= v7.5, fixes deprecations in with PHP 7.3
* Scroll is now throwing an exception when calling `current()` on an invalid iteration: always call `valid()` before
    accessing the current item, as documented in PHP's Iterator documentation [#1749](https://github.com/ruflin/Elastica/pull/1749)

### Removed
* Removed unsupported `flags` from `AbstractTermsAggregation::setInclude()` [#1766](https://github.com/ruflin/Elastica/pull/1766)
* Removed unsupported `flags` from `AbstractTermsAggregation::setExclude()` [#1766](https://github.com/ruflin/Elastica/pull/1766)
* `Terms::setMinimumMatch()` has been removed as not supported by ES 7.x


## [7.0.0-beta.3](https://github.com/ruflin/Elastica/compare/7.0.0-beta2...7.0.0-beta.3)
* Marked Elastica 5.x as unmaintained

### Bugfixes
* Fix Search::count() not counting all results [#1746](https://github.com/ruflin/Elastica/pull/1746)
* Fixed handling of Search::OPTION_SEARCH_IGNORE_UNAVAILABLE inside Scroll object

### Added
* Added `DiversifiedSampler` aggregation [#1735](https://github.com/ruflin/Elastica/pull/1735)
* Added `\Elastica\Query\DistanceFeature` [#1730](https://github.com/ruflin/Elastica/pull/1730)
* Added support for injecting a callable AWS credential provider to use static, cached, or custom-sourced credentials [#1667](https://github.com/ruflin/Elastica/pull/1667)

### Improvements
* Scroll releases previous ResultSet from memory before calling ES for next data batch [#1740](https://github.com/ruflin/Elastica/pull/1740)


## [7.0.0-beta2](https://github.com/ruflin/Elastica/compare/7.0.0-beta1...7.0.0-beta2)

### Backward Compatibility Breaks

* The method `Index::deleteById()` does not throw an `NotFoundException` when deleting a non-existing document [#1732](https://github.com/ruflin/Elastica/pull/1732)
* The class `\Elastica\QueryBuilder\Version\Version240` has been moved to `\Elastica\QueryBuilder\Version\Version700` [#1693](https://github.com/ruflin/Elastica/pull/1693)
* Dropped support for PHP 7.1 [#1703](https://github.com/ruflin/Elastica/pull/1703)

### Bugfixes

* Renamed `\Elastica\Suggest\Term` deprecated option `prefix_len` to `prefix_length` [#1707](https://github.com/ruflin/Elastica/pull/1707)
* The `\Elastica\Query\GeoPolygon::count()` method now returns the count of points passed to the filter [#1696](https://github.com/ruflin/Elastica/pull/1696)
* Fix issue in `\Elastica\Client::request()` which causes request data to not be sent to the logger [#1682](https://github.com/ruflin/Elastica/pull/1682)

### Added

* Added `geo_bounding_box`, `geo_polygon`, `match_phrase`, `match_phrase_prefix`, `match_none` to `\Elastica\QueryBuilder\Version\Version700` [#1702](https://github.com/ruflin/Elastica/pull/1702)
* Added `\Elastica\ResultSet::getTotalHitsRelation()` to get relation for total hits [#1694](https://github.com/ruflin/Elastica/pull/1694)
* Added `Sampler` aggregation [#1688](https://github.com/ruflin/Elastica/pull/1688)

### Improvements

* Launched tests with PHP 7.4 [#1704](https://github.com/ruflin/Elastica/pull/1704)
* Launched local tests with PHP 7.2 by default [#1725](https://github.com/ruflin/Elastica/pull/1725)
* Added `nullable_type_declaration_for_default_null_value`, `no_alias_functions` CS rules [#1706](https://github.com/ruflin/Elastica/pull/1706)
* Configured `visibility_required` CS rule for constants [#1723](https://github.com/ruflin/Elastica/pull/1723)
* Added `Collapse` DSL to `QueryBuilder` [#1724](https://github.com/ruflin/Elastica/pull/1724)


## [7.0.0-beta1](https://github.com/ruflin/Elastica/compare/6.1.1...7.0.0-beta1)

### Backward Compatibility Breaks
* The class `\Elastica\Type\Mapping` has been moved to `\Elastica\Mapping` [#1666](https://github.com/ruflin/Elastica/pull/1666)
* The `\Elastica\Query::$_suggest` property has been renamed to `$hasSuggest` and is now private, it should not be used from extending classes [#1679](https://github.com/ruflin/Elastica/pull/1679)
* `\Elastica\Document` expects a string as ID, not an int [#1672](https://github.com/ruflin/Elastica/pull/1672).
* Removed `\Elastica\Query\GeohashCell` query, use `\Elastica\Query\GeoBoundingBox` instead [#1672](https://github.com/ruflin/Elastica/pull/1672).
* Deprecated usage of `\Elastica\Type` class, `\Elastica\Index` class must be used instead [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Removed `\Elastica\Type` class, `\Elastica\Index` class must be used instead [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Forced index names to string in `\Elastica\Index::__construct()` [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Removed Type query `\Elastica\Query\Type` [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Removed `Elastica\Type` class, `Elastica\Index` class must be used instead [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Removed `type` handling from `Elastica\Search` class [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Removed `type` handling from `Elastica\Bulk` and `Elastica\Bulk\Action` classes [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Forced index names to string in `Elastica\Index::__construct()` [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Removed Type query `Elastica\Query\Type` [#1666](https://github.com/ruflin/Elastica/pull/1666)
* Dropped support for PHP 7.0
* \Elastica\AbstractUpdateAction::getOptions( $fields ) no longer supports the $underscore parameter, option names must match what elasticsearch expects.
* Removed no longer supported \Elastica\Query\QueryString::setAutoGeneratePhraseQueries( $bool ) [#1622](https://github.com/ruflin/Elastica/pull/1622)
* Replaced [params._agg](https://www.elastic.co/guide/en/elasticsearch/reference/master/breaking-changes-7.0.html#_replaced_literal_params__agg_literal_with_literal_state_literal_context_variable_in_scripted_metric_aggregations) with state context variable in scripted metric aggregations
* [Camel Case](https://www.elastic.co/guide/en/elasticsearch/reference/master/breaking-changes-7.0.html#_camel_case_and_underscore_parameters_deprecated_in_6_x_have_been_removed) and underscore parameters deprecated in 6.x have been removed
* The parameter [fields](https://www.elastic.co/guide/en/elasticsearch/reference/master/breaking-changes-7.0.html#_the_parameter_literal_fields_literal_deprecated_in_6_x_has_been_removed_from_bulk_request) deprecated in 6.x has been removed from Bulk requestedit and Update request.
* The [_parent](https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-parent-field.html) field has been removed in favour of the join field.
* hits.total is now an object in the search response [hits.total](https://www.elastic.co/guide/en/elasticsearch/reference/master/breaking-changes-7.0.html#_literal_hits_total_literal_is_now_an_object_in_the_search_response)
* Elastica\Reindex does not return an Index anymore but a Response.
* Elastica\Reindex->run() does not refresh the new Index after completion anymore. Use `$reindex->setParam(Reindex::REFRESH, 'wait_for')` instead.
* `Elastica\Search->search()` and `Elastica\Search->count()` use request method `POST` by default. Same for `Elastica\Index`, `Elastica\Type\AbstractType`, `Elastica\Type`.
* Elastica\Client `$_config` field is now a `ClientConfiguration` instead of an array
* Removed `\Elastica\Client::_log`, `\Elastica\Log` and the `log` configuration option. Use the `Psr\Log\LoggerInterface $logger` client argument to customize logging.
* Changed all factory methods to make use of [late static bindings](http://docs.php.net/manual/en/language.oop5.late-static-bindings.php) by using `static` instead of `self` keyword. This is to increase extendability for classes with factory methods.


### Bugfixes

* Always set the Guzzle `base_uri` to support connecting to multiple ES hosts. [#1618](https://github.com/ruflin/Elastica/pull/1618)
* Properly handle underscore prefixes in options and bulk request metadata ([cf upstream](https://github.com/elastic/elasticsearch/issues/26886). [#1621](https://github.com/ruflin/Elastica/pull/1621)
* Preserve zeros while doing float serialization to JSON. [#1635](https://github.com/ruflin/Elastica/pull/1635)
* Add ```settings``` level on json to create an Index in all tests (it worked till 6.x but it shouldn't work)


### Added

* support for elasticsearch-php ^7.0
* Added `ParentAggregation` [#1616](https://github.com/ruflin/Elastica/pull/1616)
* Elastica\Reindex missing options (script, remote, wait_for_completion, scroll...)
* Added `AdjacencyMatrix` aggregation [#1642](https://github.com/ruflin/Elastica/pull/1642)
* Added request method parameter to `Elastica\SearchableInterface->search()` and `Elastica\SearchableInterface->count()`. Same for `Elastica\Search`[#1441](https://github.com/ruflin/Elastica/issues/1441)
* Added support for Field Collapsing (Issue: [#1392](https://github.com/ruflin/Elastica/issues/1392); PR: [#1653](https://github.com/ruflin/Elastica/pull/1653))
* Support string DSN in `\Elastica\Client` constructor for config argument [#1640](https://github.com/ruflin/Elastica/issues/1640)
* Move Client configuration in a dedicated class
* Added `callable` type hinting to `$callback` in `Client` constructor. [#1659](https://github.com/ruflin/Elastica/pull/1659)
* Added `setTrackTotalHits` method to `Elastica\Query`[#1663](https://github.com/ruflin/Elastica/issues/1663)
* Allow metadata to be set on Aggregations (via `AbstractAggregation::setMeta(array)`). [#1677](https://github.com/ruflin/Elastica/issues/1677)


### Improvements

* Added `native_function_invocation` CS rule [#1606](https://github.com/ruflin/Elastica/pull/1606)
* Elasticsearch test version changed from 6.5.2 to 6.6.1 [#1620](https://github.com/ruflin/Elastica/pull/1620)
* Clear scroll context also when empty page was received [#1660](https://github.com/ruflin/Elastica/pull/1660)


## [6.1.1](https://github.com/ruflin/Elastica/compare/6.1.0...6.1.1)

### Added

* The preferred type name is [_doc](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/removal-of-types.html), so that index APIs have the same path as they will have in 7.0
* Added `BucketSelector` aggregation [#1554](https://github.com/ruflin/Elastica/pull/1554)
* Added `DerivativeAggregation` [#1553](https://github.com/ruflin/Elastica/pull/1553)
* The preferred type name is [_doc](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/removal-of-types.html), so that index APIs have the same path as they will have in 7.0
* Introduced new version of PHP-CS-Fixer and new Lint travis step. [#1555](https://github.com/ruflin/Elastica/pull/1555)
* Added `typed_keys` support for Search queries [#1603](https://github.com/ruflin/Elastica/pull/1603)

### Improvements

* Reduced memory footprint of response by not keeping the raw JSON data when JSON after JSON has been parsed. [#1588](https://github.com/ruflin/Elastica/pull/1588)

### Deprecated
* Index templates use index_patterns instead of [template](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/breaking-changes-6.0.html#_index_templates_use_literal_index_patterns_literal_instead_of_literal_template_literal)

## [6.1.0](https://github.com/ruflin/Elastica/compare/6.0.2...6.1.0)

### Backward Compatibility Breaks

* Made result sets adhere to `\Iterator` interface definition that they implement. Specifically, you need to call `valid()` on the result set before calling `current()`. When using `foreach` this is done by PHP automatically. When `valid` returns false, the return value of `current` is undefined instead of false. [#1506](https://github.com/ruflin/Elastica/pull/1506)
  * `\Elastica\ResultSet::next` returns `void` instead of `\Elastica\Result|false`
  * `\Elastica\Bulk\ResponseSet::current` returns `\Elastica\Bulk\Response` instead of `\Elastica\Bulk\Response|false`
  * `\Elastica\Multi\ResultSet::current` returns `\Elastica\ResultSet` instead of `\Elastica\ResultSet|false`

### Added

* Added a transport class for mocking a HTTP 403 error codes, useful for testing response failures in inheriting clients [#1529](https://github.com/ruflin/Elastica/pull/1529)
* [Field](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-function-score-query.html#function-random) param for `Elastica\Query\FunctionScore::addRandomScoreFunction` [#1529](https://github.com/ruflin/Elastica/pull/1529)
* [Index Recovery](https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-recovery.html) : the indices recovery API provides insight into on-going index shard recoveries. It was never been implemented into Elastica. [#1537](https://github.com/ruflin/Elastica/pull/1537)
* add parent_id (reference [#1518](https://github.com/ruflin/Elastica/issues/1518)) in QueryBuilder. [#1533]([#1518](https://github.com/ruflin/Elastica/issues/1533))
* implemented ```string_distance``` option in Term Suggestion [#1543](https://github.com/ruflin/Elastica/pull/1543)

### Improvements

* Using `Elastica\Query\FunctionScore::addRandomScoreFunction` without `$field` parameter is deprecated since ES 6.0 and will fail since ES 7.0 [#1522](https://github.com/ruflin/Elastica/pull/1522)
* `Aggreation\Percentiles` updated to a newer version of the Algorithm (T-Digest 3.2) and Percentiles results changed a bit Have a [look at here](https://github.com/elastic/elasticsearch/pull/28305), so updated tests in order not to fail. [#1531]([#1352](https://github.com/ruflin/Elastica/pull/1531))
* `Aggregation\Percentiles` have been updated since [Elasticsearch 2.3](https://www.elastic.co/guide/en/elasticsearch/reference/2.3/search-aggregations-metrics-percentile-aggregation.html). In this version `compression, HDR histogram` changed their implementations. The `missing` field has never been implemented. [#1532](https://github.com/ruflin/Elastica/pull/1532)

  Before
  ```json
    "compression" : 200,
    "method" : "hdr",
    "number_of_significant_value_digits" : 3
  ```

  Now
  ```json
    "tdigest": {
      "compression" : 200
    },
    "hdr": {
      "number_of_significant_value_digits" : 3
    }
  ```

* Never implemented the method *Missing* on [`Aggregation\Percentiles`](https://www.elastic.co/guide/en/elasticsearch/reference/6.4/search-aggregations-metrics-percentile-aggregation.html) [#1532](https://github.com/ruflin/Elastica/pull/1532)

## [6.0.2](https://github.com/ruflin/Elastica/compare/6.0.1...6.0.2)

### Added

* Added support for pipeline when indexing document. [#1455](https://github.com/ruflin/Elastica/pull/1455)
* Added support for multiple bucket sort orders for aggregations. [#1480](https://github.com/ruflin/Elastica/pull/1480)
* Added basic support for the Elasticsearch Task Api
* Added updateByQuery endpoint. [#1499](https://github.com/ruflin/Elastica/pull/1499)

### Improvements

* Use `source` script field instead of deprecated (since ES 5.6) `inline` field. [#1497](https://github.com/ruflin/Elastica/pull/1497)
* Updated Elasticsearch testing version to 6.2.4. [#1501](https://github.com/ruflin/Elastica/pull/1501)


## [6.0.1](https://github.com/ruflin/Elastica/compare/6.0.0...6.0.1)

### Bugfixes
- Characters "<" and ">" will be removed when a query term is passed to [`Util::escapeTerm`](https://github.com/ruflin/Elastica/pull/1415/files). Since v5.1 the [documentation](https://www.elastic.co/guide/en/elasticsearch/reference/5.1/query-dsl-query-string-query.html#_reserved_characters) states that these symbols cannot be escaped ever.
- Remove [`each()`](http://www.php.net/each) usage to fix PHP 7.2 compatibility
- Fix [#1435](https://github.com/ruflin/Elastica/issues/1435) forcing `doc_as_upsert` to be boolean, acording [Elastic doc-update documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update.html#_literal_doc_as_upsert_literal)
- Fix [#1456](https://github.com/ruflin/Elastica/issues/1456) set SSL as connection scheme if it is required

### Added

* Added request parameters to `Client->deleteDocuments()`. [#1419](https://github.com/ruflin/Elastica/pull/1419)
* Added request parameters to `Type->updateDocuments()`, `Type->addDocuments()`, `Type->addObjects()`, `Index->addDocuments()`, `Index->updateDocuments()`. [#1427](https://github.com/ruflin/Elastica/pull/1427)
* Added avg_bucket() and sum_bucket() in aggregations [PR#1443](https://github.com/ruflin/Elastica/pull/1443) - (https://github.com/ruflin/Elastica/issues/1279)
* Added support for [terms lookup mechanism](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html#query-dsl-terms-lookup) on terms query [#1452](https://github.com/ruflin/Elastica/pull/1452)


## [6.0.0](https://github.com/ruflin/Elastica/compare/6.0.0-beta1...6.0.0)

### Backward Compatibility Breaks
- Return the [_source of inner hit nested](https://github.com/elastic/elasticsearch/pull/26982) as is without wrapping it into its full path context [#1398](https://github.com/ruflin/Elastica/pull/1398)
- Removed CrossIndex Class as from now use Reindex. [#1411](https://github.com/ruflin/Elastica/pull/1411)

### Added

- Added clear() to `Scroll` for closing search context on ES manually
- Added Elastica\Aggregation\StatsBucket

### Improvements

- Clear search context on ES after usage in `Scroll`


## [6.0.0-beta1](https://github.com/ruflin/Elastica/compare/5.3.0...6.0.0-beta1)

### Backward Compatibility Breaks

- Numeric to and from parameters in [date_range aggregation](https://www.elastic.co/guide/en/elasticsearch/reference/6.0/breaking_60_aggregations_changes.html#_numeric_literal_to_literal_and_literal_from_literal_parameters_in_literal_date_range_literal_aggregation_are_interpreted_according_to_literal_format_literal_now) are interpreted according to format of the target field
- In ES6 only [strict type boolean](https://github.com/elastic/elasticsearch/pull/22200) are accepted. [On ES6 docs](https://www.elastic.co/guide/en/elasticsearch/reference/6.0/boolean.html)
- removed analyzed/not_analyzed on [indices mapping](https://www.elastic.co/guide/en/elasticsearch/reference/6.0/mapping-index.html)
- [store](https://www.elastic.co/guide/en/elasticsearch/reference/6.0/mapping-store.html) field only accepts boolean
- Replace IndexAlreadyExistsException with [ResourceAlreadyExistsException](https://github.com/elastic/elasticsearch/pull/21494) [#1350](https://github.com/ruflin/Elastica/pull/1350)
- in order to delete an index you should not delete by its alias now you should delete using the [concrete index name](https://github.com/elastic/elasticsearch/blob/6.0/core/src/test/java/org/elasticsearch/aliases/IndexAliasesIT.java#L445) [#1348](https://github.com/ruflin/Elastica/pull/1348)
- Removed ```optimize``` from Index class as it has been deprecated in ES 2.1 and removed in [ES 5.x+](https://www.elastic.co/guide/en/elasticsearch/reference/2.4/indices-optimize.html) use forcemerge [#1351](https://github.com/ruflin/Elastica/pull/1350)
- In QueryString is not allowed to use fields parameters in conjunction with default_field parameter. This is not well documented, it's possibile to understand from [Elasticsearch tests :  QueryStringQueryBuilderTests.java](https://github.com/elastic/elasticsearch/blob/6.0/core/src/test/java/org/elasticsearch/index/query/QueryStringQueryBuilderTests.java#L917) [#1352](https://github.com/ruflin/Elastica/pull/1352)
- Index mapping field of type [*'string'*](https://www.elastic.co/guide/en/elasticsearch/reference/5.5/string.html) has been removed from Elasticsearch 6.0 codebase [#1353](https://github.com/ruflin/Elastica/pull/1353)
- The [created and found](https://github.com/elastic/elasticsearch/pull/25516) fields in index and delete responses became obsolete after the introduction of the result field in index, update and delete responses [#1354](https://github.com/ruflin/Elastica/pull/1354)
- Removed file scripts [#24627](https://github.com/elastic/elasticsearch/pull/24627) [#1364](https://github.com/ruflin/Elastica/pull/1364)
- Removed [groovy script](https://github.com/elastic/elasticsearch/pull/21607) [#1364](https://github.com/ruflin/Elastica/pull/1364)
- Removed [native script](https://github.com/elastic/elasticsearch/pull/24726) [#1364](https://github.com/ruflin/Elastica/pull/1364)
- Removed old / removed script language support : javascript, python, mvel [#1364](https://github.com/ruflin/Elastica/pull/1364)
- Disable [_all](https://github.com/elastic/elasticsearch/pull/22144) by default, disallow configuring _all on 6.0+ indices [#1365](https://github.com/ruflin/Elastica/pull/1365)
- [Unfiltered nested source](https://github.com/elastic/elasticsearch/pull/26102) should keep its full path [#1366](https://github.com/ruflin/Elastica/pull/1366)
- The deprecated minimum_number_should_match parameter in the bool query has been removed, use minimum_should_match instead. [#1369](https://github.com/ruflin/Elastica/pull/1369)
- For geo_distance queries, sorting, and aggregations the sloppy_arc option has been removed from the distance_type parameter. [#1369](https://github.com/ruflin/Elastica/pull/1369)
- The geo_distance_range query, which was deprecated in 5.0, has been removed. [#1369](https://github.com/ruflin/Elastica/pull/1369)
- The optimize_bbox parameter has been removed from geo_distance queries. [#1369](https://github.com/ruflin/Elastica/pull/1369)
- The disable_coord parameter of the bool and common_terms queries has been removed. If provided, it will be ignored and issue a deprecation warning. [#1369](https://github.com/ruflin/Elastica/pull/1369)
- [Unfiltered nested source](https://github.com/elastic/elasticsearch/pull/26102) should keep its full path [#1366](https://github.com/ruflin/Elastica/pull/1366)
- [Analyze Explain](https://www.elastic.co/guide/en/elasticsearch/reference/6.0/_explain_analyze.html) no more support [request parameters](https://www.elastic.co/guide/en/elasticsearch/reference/5.5/indices-analyze.html), use request body instead. [#1370](https://github.com/ruflin/Elastica/pull/1370)
- [Mapper Attachment plugin has been removed](https://github.com/elastic/elasticsearch/pull/20416) Use Ingest-attachment plugin and attachment processors with pipeline to ingest new documents. [#1375](https://github.com/ruflin/Elastica/pull/1375)
- [Indices](https://github.com/elastic/elasticsearch/pull/21837) Query has been removed in Elasticsearch 6.0 [#1376](https://github.com/ruflin/Elastica/pull/1376)
- Remove deprecated [type and slop](https://github.com/elastic/elasticsearch/pull/26720) field in match query [#1382](https://github.com/ruflin/Elastica/pull/1382)
- Remove [several parse field](https://github.com/elastic/elasticsearch/pull/26711) deprecations in query builders [#1382](https://github.com/ruflin/Elastica/pull/1382)
- Remove [deprecated parameters](https://github.com/elastic/elasticsearch/pull/26508) from ids_query [#1382](https://github.com/ruflin/Elastica/pull/1382)
- Implemented [join-datatype](https://www.elastic.co/guide/en/elasticsearch/reference/current/parent-join.html) is a special field that creates parent/child relation within documents of the same index. [#1383](https://github.com/ruflin/Elastica/pull/1383)

### Bugfixes
- Enforce [Content-Type requirement on the layer Rest](https://github.com/elastic/elasticsearch/pull/23146), a [PR on Elastica #1301](https://github.com/ruflin/Elastica/issues/1301) solved it (it has been implemented only in the HTTP Transport), but it was not implemented in the Guzzle Transport. [#1349](https://github.com/ruflin/Elastica/pull/1349)
- Scroll no longer does an extra iteration both on an empty result and on searches where the last page has a significantly smaller number of results than the pages before it.

### Added

- Added `Query\SpanContaining`, `Query\SpanWithin` and `Query\SpanNot` [#1319](https://github.com/ruflin/Elastica/pull/1319)
- Implemented [Pipeline](https://www.elastic.co/guide/en/elasticsearch/reference/current/pipeline.html) and [Processors](https://www.elastic.co/guide/en/elasticsearch/reference/current/ingest-processors.html). [#1373](https://github.com/ruflin/Elastica/pull/1373)
- In PHP 7.2 count() now raises a warning when an invalid parameter is passed. Only arrays and objects implementing the Countable interface should be passed. [#1378](https://github.com/ruflin/Elastica/pull/1378)


## [5.3.0](https://github.com/ruflin/Elastica/compare/5.2.1...5.3.0)

### Backward Compatibility Breaks

- Removed `Query\NumericRange`, use `Query\Range` instead [#1334](https://github.com/ruflin/Elastica/pull/1334)

### Bugfixes

- Send the `scroll_id` inside a json body instead of plain text [#1325](https://github.com/ruflin/Elastica/pull/1325)

### Added
 - Added getNumberOfReplicas() for index settings [PR#1324](https://github.com/ruflin/Elastica/pull/1324)
 - Added getNumberOfShards() for index settings [PR#1321](https://github.com/ruflin/Elastica/pull/1331)
 - Added `\Elastica\Query\Span*` for proximity searches [#304](https://github.com/ruflin/Elastica/issues/304)
 - Added avg_bucket() and sum_bucket() in aggregations [PR#1443](https://github.com/ruflin/Elastica/pull/1443) - (https://github.com/ruflin/Elastica/issues/1279)


## [5.2.1](https://github.com/ruflin/Elastica/compare/5.2.0...5.2.1)

### Bugfixes

- Fix elastic 5.3.x deprecation warning related to Content-Type not being set.
- Fix updating settings of an index. [#1296](https://github.com/ruflin/Elastica/pull/1296)
- Remove `Elastica\Search::OPTION_SEARCH_TYPE_DFS_QUERY_AND_FETCH` and `Elastica\Search::OPTION_SEARCH_TYPE_QUERY_AND_FETCH` as no longer supported as of 5.3.0
- Fix bad parameter value to refresh document [#1318](https://github.com/rufli/Elastica/pull/1318)

### Added

 - Parameter `filter_path` for response filtering (e.g. `$index->search($query, ['filter_path' => 'hits.hits._source'])`)
 - Add support for Health parameters for Cluster\Health endpoint (new prop : delayed_unassigned_shards, number_of_pending_tasks, number_of_in_flight_fetch, task_max_waiting_in_queue_millis, active_shards_percent_as_number)
 - Add support for querystring in Type. this allow to use `update_all_types` in type mapping in order to resolve conflicts between fields in different types. [Conflicts between fields in different types](https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-put-mapping.html#merging-conflicts)
 - Added `\Elastica\Query\ParentId` to avoid join with parent documents [#1287](https://github.com/ruflin/Elastica/issues/1287)
 - Added `\Elastica\Reindex` for reindexing between indices [#1311](https://github.com/ruflin/Elastica/issues/1311)

### Improvements

 - Added support for `other_bucket` and `other_bucket_key` paramters on `Elastica\Aggregation\Filters`
 - Update elasticsearch testing dependency to 5.4.1

### Deprecated
 - Deprecated `Tool\CrossIndex` use `\Elastica\Reindex` instead [#1311](https://github.com/ruflin/Elastica/issues/1311)

## [5.2.0](https://github.com/ruflin/Elastica/compare/5.1.0...5.2.0)

### Bugfixes

- Fix reading bool index settings like `\Elastica\Index\Settings::getBlocksWrite`. Elasticsearch returns all settings as strings and does not normalize bool values.
  The getters now return the right bool value for whichever string representation is used like 'true', '1', 'on', 'yes'. [#1251](https://github.com/ruflin/Elastica/pull/1251)
- Fix for QueryBuilder version check `\Elastica\QueryBuilder\Version\Version240.php` added all new query types to queries array. [#1266](https://github.com/ruflin/Elastica/pull/1266) [#1269](https://github.com/ruflin/Elastica/pull/1269)
- Do not modify the original query in `\Elastica\Search::count`. [#1276](https://github.com/ruflin/Elastica/pull/1276)

### Added

- Added `\Elastica\Client::requestEndpoint`, `\Elastica\Index::requestEndpoint`, `\Elastica\Type::requestEndpoint` that allow make requests with official client Endpoint usage. [#1275](https://github.com/ruflin/Elastica/pull/1275)
- Added `\Elastica\Aggregation\GeoBounds` that computes the bounding box containing all geo_point values for a field. [#1271](https://github.com/ruflin/Elastica/pull/1271)
- Added `\Elastica\Query\MatchNone` the inverse of MatchAll. [#1276](https://github.com/ruflin/Elastica/pull/1276)

### Improvements

- added support for the "explain" flag of AnalyzeAPI [#1254](https://github.com/ruflin/Elastica/pull/1254)
- added support for the "request_cache" search option [#1243](https://github.com/ruflin/Elastica/pull/1243)
- skip sending "retry_on_conflict=0" default query param to improve compatibility with Amazon Elasticsearch [#1047](https://github.com/ruflin/Elastica/pull/1047)
- optimized `\Elastica\Scroll` to avoid one request [#1273](https://github.com/ruflin/Elastica/pull/1273)
- Update elasticsearch-php dependency to 5.2.0 [#1245](https://github.com/ruflin/Elastica/pull/1245)
- Update elasticsearch testing dependency to 5.2.2 [#1245](https://github.com/ruflin/Elastica/pull/1245)

### Deprecated

- Deprecated `\Elastica\Exception\ElasticsearchException` which is irrelevant since Elasticsearch now exposes the errors as a structured array instead of a single string.
  Use `\Elastica\Exception\ResponseException::getResponse::getFullError` instead.
- Deprecated both `prefix_len` & `min_word_len` fields in `Elastica\Suggest\CandidateGenerator\DirectGenerator` as these now return errors when using the phrase suggester to querying terms.
  Use `prefix_length` & `min_word_length` instead [#1282](https://github.com/ruflin/Elastica/pull/1282)
  Use `\Elastica\Exception\ResponseException::getResponse::getFullError` instead. [#1251](https://github.com/ruflin/Elastica/pull/1251)

## [5.1.0](https://github.com/ruflin/Elastica/compare/5.0.0...5.1.0)

### Backward Compatibility Breaks

- `\Elastica\Script\AbstractScript` added the script language as constructor argument and sub-classes must implement `getScriptTypeArray`

### Bugfixes

- Removed features that do not exist in Elasticsearch 5.0 anymore:
  - `ttl` and `timestamp` logic: setters and getters in documents and mapping
  - `\Elastica\Query\Missing`: negate `\Elastica\Query\Exists` instead
  - `\Elastica\Query\TopChildren`
- `\Elastica\Query\MatchPhrase` and `\Elastica\Query\MatchPhrasePrefix` do not extend `\Elastica\Query\Match` anymore because they do not share exactly the same options
- Removed the `routing` option in `\Elastica\Index::create` because there is no routing param when creating an index. So that option was doing nothing so far but fails in Elasticearch 5.0 because the non-existing query param is validated.
- Fix `relation` property of `\Elastica\Query\GeoShapeProvided`
- repoint `\Elastica\Type::exists` from the deprecated /{index}/{type} endpoint to /{index}/_mapping/{type}

### Added

- added `\Elastica\Script\ScriptId` to reference stored scripts by ID
- added `\Elastica\Query\AbstractGeoShape::RELATION_WITHIN`
- Date math in index names is now escaped in URI
- Added a check for paths that already have date math escaped

### Improvements

- `\Elastica\Query\HasParent` to use `parent_type` instead of `type`. Fixes warning due to field being deprecated.

### Deprecated

- Deprecated functionality that is also deprecated in Elasticsearch 5.0:
  - `\Elastica\Client::optimizeAll` in favor of `\Elastica\Client::forcemergeAll`
  - `\Elastica\Query\BoolQuery::setMinimumNumberShouldMatch` in favor of `\Elastica\Query\BoolQuery::setMinimumShouldMatch`
  - `\Elastica\Query\GeoDistanceRange`: use distance aggregations or sorting instead
  - `\Elastica\Query\GeohashCell`
  - `\Elastica\Query\Indices`: search on the `_index` field instead
  - `\Elastica\Query\Match::setFieldType`: use `\Elastica\Query\MatchPhrase` and `\Elastica\Query\MatchPhrasePrefix` instead
- `\Elastica\Transport\Null` is deprecated because null is a reserved class name in PHP 7. Use `\Elastica\Transport\NullTransport` instead.

## [5.0.0](https://github.com/ruflin/Elastica/compare/5.0.0-beta1...5.0.0)

### Backward Compatibility Fixes
- Updated Elastica\Test\Suggest\CompletionTest now payload and output are removed
- Updated Elastica\Test\TypeTest::testGetDocumentWithFieldsSelection The stored_fields parameter will only return stored fields — it will no longer extract values from the _source
- remove _shutdown for Node and Cluster as deprecated

### Bugfixes
- Query options such as "timeout" or "terminate_after" should not be ignored when using Multi\Search

### Added
- Added regex option form suggest completions https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html#regex

### Improvements
- `\Elastica\JSON` throws exception with readable message instead of errno
- `\Elastica\JSON::stringify` throws `\Elastica\Exception\JSONParseException` on error


## [5.0.0-beta1](https://github.com/ruflin/Elastica/compare/3.2.3...5.0.0-beta1)

### Backward Compatibility Breaks
- Update elasticsearch dependency to 5.0
- Replace flush refresh param with a options array
- Rename Mapping::setFields to Mapping::setStoredFields
- Removing all deprecated filters including tests. Use queries instead.
- Remove deprecated Elastica\Script*.php classes. Use Elastica\Script\* instead.
- Remove Elastica/Query/Image.php and test/Elastica/Query/ImageTest.php, no more support for image-plugin.
- Remove Elastica/Query/Filtered.php and test/Elastica/Query/FilteredTest.php and all uses from code.
- Remove index.merge.policy.merge_factor, and set/get MergePolicy as it looks deprecated from ES 1.6
- Add new "Percolate query" functionality and tests
- Remove in Elastica\AbstractUpdateAction Option "percolate", getter and setter as deprecated as of ES 1.3. Use Percolator instead.
- Remove in Elastica\Aggregation\DateHistogram Option "pre_zone" is deprecated as of ES 1.5. Use "time_zone" instead
- Remove in Elastica\Aggregation\DateHistogram Option "post_zone" is deprecated as of ES 1.5. Use "time_zone" instead.
- Remove in Elastica\Aggregation\DateHistogram Option "pre_zone_adjust_large_interval" is deprecated as of ES 1.5. Use "time_zone" instead.
- Remove in Elastica\Aggregation\DateHistogram Option "pre_offset" is deprecated as of ES 1.5. Use "offset" instead.
- Remove in Elastica\Aggregation\DateHistogram Option "post_offset" is deprecated as of ES 1.5. Use "offset" instead.
- Remove Elastica\Document::add as deprecated. Use Elastica\Document::set instead
- Remove Elastica\Document::setScript() is no longer available as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate.
- Remove Elastica\Document::getScript() is no longer available as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate.
- Remove Elastica\Document::hasScript() is no longer available as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate.
- Remove Elastica/Query::setLimit as deprecated. Use the Elastica/Query::setSize() method
- Remove Elastica\Query\Builder
- Remove Elastica\Query\Fuzzy::addField as deprecated. Use Elastica\Query\Fuzzy::setField and Elastica\Query\FuzzysetFieldOption instead.
- Remove Elastica\Query::setIds as deprecated. Use Elastica\Query::like instead.
- Remove Elastica\Query::setLikeText as deprecated. Use Elastica\Query::like instead.
- Remove Elastica\Query Option "percent_terms_to_match" is deprecated as of ES 1.5. Use "minimum_should_match" instead.
- Remove Elastica\QueryBuilder\DSL\Query "More Like This Field" query is deprecated as of ES 1.4. Use MoreLikeThis query instead.
- Changed visibility from protected to private Elastica\ResultSet::$_position as accessing this property in an extended class is deprecated.
- Changed visibility from protected to private Elastica\ResultSet::$_response as accessing this property in an extended class is deprecated.
- Changed visibility from protected to private Elastica\ResultSet::$_query as accessing this property in an extended class is deprecated.
- Changed visibility from protected to private Elastica\ResultSet::$_results as accessing this property in an extended class is deprecated.
- Removed Elastica\ResultSet::$_timedOut as deprecated. Use ResultSet->hasTimedOut() instead.
- Removed Elastica\ResultSet::$_took as deprecated. Use ResultSet->hasTimedOut() instead.
- Removed Elastica\ResultSet::$_totalHits as deprecated. Use ResultSet->hasTimedOut() instead.
- Removed Elastica\Type::delete() It is no longer possible to delete the mapping for a type. Instead you should delete the index and recreate it with the new mappings.
- Removed Elastica\Query\Builder as deprecated. Use new Elastica\QueryBuilder instead.
- Removed Elastica\Percolator as deprecated. Use new Elastica\Query\Percolate instead.
- Changed Elastica\Index::deleteByQuery() to use new API https://www.elastic.co/guide/en/elasticsearch/reference/5.0/docs-delete-by-query.html
- Remove Elastica\ScanAndScroll and test, Scan search type is removed from ElasticSearch 5.0.
- Remove support for PHP 5.4 and 5.5. Require at least PHP 5.6 #1202
- Remove groovy as default scripting language
- Remove search_type=count as it is removed in Elasticsearch 5.0
- Remove fielddata_fields as it has been deprecated in ES5, use parameter docvalue_fields instead

### Added
- Elastica\QueryBuilder\DSL\Query::exists
- Elastica\QueryBuilder\DSL\Query::type

### Improvements
- Add a constant for the expression language.
- `Health::getIndices` returns key=>value result, where key === $indexName.
```
$cluster->getHealth()->getIndices()[$indexName]
// or
$indices = $cluster->getHealth()->getIndices();
$indices[$indexName]
```
- Added a `Query::setTrackScores` method
- Implemented painless as default scripting language in tests
- Updated Dockerfile and elasticsearch.yml to allow inline.script: true
- Updated some Script function to use groovy as now default scripting is painless
    - Elastica\Test\Aggregation\ScriptTest::testAggregationScript
    - Elastica\Test\Aggregation\ScriptTest::testAggregationScriptAsString
    - Elastica\Test\Query\FunctionScoreTest::testScriptScore
    - Elastica\Test\BulkTest::testUpdate
    - Elastica\Test\ClientTest::testUpdateDocumentByScript
    - Elastica\Test\ClientTest::testUpdateDocumentByScriptWithUpsert
    - Elastica\Test\ClientTest::testUpdateDocumentPopulateFields
    - Elastica\Test\ClientTest::testUpdateDocumentPopulateFields
    - Elastica\Test\TypeTest::testUpdateDocument
    - Elastica\Test\TypeTest::testUpdateDocumentWithIdForwardSlashes
    - Elastica\Test\TypeTest::testUpdateDocumentWithParameter
    - Elastica\Test\TypeTest::testUpdateDocumentWithFieldsSource
- Composer installations will no longer include tests and other development files.


## [3.2.3](https://github.com/ruflin/Elastica/compare/3.2.2...3.2.3)

### Bugfixes
- Query builder is now compatible with Elasticsearch 2.X

### Added
- Elastica\Aggregation\BucketScript
- Elastica\Aggregation\SerialDiff
- Elastica\Query\InnerHits

### Improvements
- Elastica\Client constructor now accepts a transport of fully qualified name. [#1169](https://github.com/ruflin/Elastica/pull/1169)
- Update Elasticsearch dependency to 2.4.0


## [3.2.2](https://github.com/ruflin/Elastica/compare/3.2.1...3.2.2)

### Backward Compatibility Fixes

### Bugfixes
- Set HTTP headers on each request preventing server error if persistent connection is enabled and compression enabled and later disabled for the same connection.
- Removed `int` type hinting in `setMinimumMatch` (`Terms` Query): it should also allow `string`. [#1151](https://github.com/ruflin/Elastica/pull/1151)

### Added
- Elastica\QueryBuilder\DSL\Query::geo_distance
- Elastica\Aggregation\GeoCentroid [#1150](https://github.com/ruflin/Elastica/pull/1150)
- [Multi value field](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-function-score-query.html#_multi_values_fields) param for decay function.
- Elastica\Client::getVersion [#1152](https://github.com/ruflin/Elastica/pull/1152)
- Added support for terminate_after parameter in search queries [#1168](https://github.com/ruflin/Elastica/pull/1168)

### Improvements
- Set PHP 7.0 as default development version
- Get the root reason from Elasticsearch's error JSON, when available [#1111](https://github.com/ruflin/Elastica/pull/1111)
- Optimize memory usage for Http Adapter [#1161](https://github.com/ruflin/Elastica/pull/1161)

### Changed
- Remove JSON_ELASTICSEARCH constant as not needed anymore

## [3.2.1](https://github.com/ruflin/Elastica/compare/3.2.0...3.2.1)

### Backward Compatibility Fixes
- Reintroduced properties in ResultSet removed in 3.2.0 as deprecated properties to be removed in 4.0

### Bugfixes
- Fix fatal error on `Query::addScriptField()` if scripts were already set via `setScriptFields()` [#1086](https://github.com/ruflin/Elastica/pull/1086)
- Fix namespace collision of `Type` in `Query\Ids` [#1104](https://github.com/ruflin/Elastica/pull/1104)

### Added
- Added the concept of ResultSet Transformers. The Transformer adds more information to a Result, for example the original object or data that created the Result. [#1066](https://github.com/ruflin/Elastica/pull/1066)
- Tidied property initialisation in classes where it was duplicated

## [3.2.0](https://github.com/ruflin/Elastica/compare/3.1.1...3.2.0)

### Backward Compatibility Breaks
- Method \Elastica\ResultSet::create and property \Elastica\ResultSet::$class were removed. To change the ResultSet class, implement your own ResultSet Builder. [#1065](https://github.com/ruflin/Elastica/pull/1065)
- Properties on \Elastica\ResultSet _totalHits, _maxScore, _took and _timedOut that were originally set on object construction are now accessed by the getters on the ResultSet. [#1065](https://github.com/ruflin/Elastica/pull/1065)

### Bugfixes
- Fix php notice on `\Elastica\Index::getAliases()` if index has no aliases [#1078](https://github.com/ruflin/Elastica/issues/1078)

### Added
- Update elasticsearch build dependency to elasticsearch 2.3.2 [#1084](https://github.com/ruflin/Elastica/pull/1084)

### Improvements
- `Elastica\Type->deleteByQuery($query, $options)` $query param can be a query `array` again https://github.com/ruflin/Elastica/issues/1072 [#1073](https://github.com/ruflin/Elastica/pull/1073)
- `Elastica\Client->connect()` allows to establish a connection to ES server when the config was set using method `Elastica\Client->setConfigValue()` https://github.com/ruflin/Elastica/issues/1076 [#1077](https://github.com/ruflin/Elastica/pull/1077)
- Elastica\Client constructor now accepts a LoggerInterface and will log both successful and failed requests. [#1069](https://github.com/ruflin/Elastica/pull/1069)

### Deprecated
- Configuring the logger in \Elastica\Client $config constructor is deprecated and will be removed. Use the $logger argument instead. [#1069](https://github.com/ruflin/Elastica/pull/1069)
- Extracted creation of ResultSet objects to a new dedicated ResultSet\Builder implementation. [#1065](https://github.com/ruflin/Elastica/pull/1065)
- All properties in the \Elastica\ResultSet class will be moved to private in 4.0. To manipulate the creation of a ResultSet, implement the \Elastica\ResultSet\BuilderInterface and pass your new Builder to the \Elastica\Search instances. [#1065](https://github.com/ruflin/Elastica/pull/1065)


## [3.1.1](https://github.com/ruflin/Elastica/compare/3.1.0...3.1.1)

### Added
- Add an "AwsAuthV4" transport that automatically signs requests using credentials from the environment or from the client config. This allows using Elastica with Amazon ElasticSearch Service domains that are restricted to IAM roles or policies. [#1056](https://github.com/ruflin/Elastica/pull/1056)
- Update elasticsearch build dependency to elasticsearch 2.2.1

### Improvements
- `Elastica\Exception\InvalidException` will be thrown if you try using an
  `Elastica\Aggregation\AbstractSimpleAggregation` without setting either the
  `field` or `script` param.
- `Elastica\Index->deleteByQuery($query, $options)` $query param can be a query `array` again
- `Elastica\Query\MoreLikeThis->toArray()` now supports providing a non-indexed document as an input to perform the comparison.
- `Elastica\Status` will lazy load the `_stats` at when it is needed. https://github.com/ruflin/Elastica/pull/1058


## [3.1.0](https://github.com/ruflin/Elastica/compare/3.0.1...3.1.0)

### Backward Compatibility Breaks
- Update Guzzle transport to use Guzzle 6
- Elastica\Query\FunctionScore::setFilter - deprecated and will throw DeprecatedException since not supported by Elasticsearch. Use setQuery instead.

### Added
- `Elastica\Result->getDocument` and `Elastica\ResultSet->getDocuments` for return `\Elastica\Document`. https://github.com/ruflin/Elastica/issues/960

### Improvements
- Add username and password params to connection

### Deprecated
- Elastica\AbstractScript|Script|ScriptFile|ScriptFields deprecated in favor of Elastica\Script|AbstractScript|Script|ScriptFile|ScriptFields [#1028](https://github.com/ruflin/Elastica/pull/1028)
- Elastica\Filter\* are deprecated. You can use proper queries instead. Backward compatibility layer provided, but will be removed in next Elastica releases. See https://www.elastic.co/blog/better-query-execution-coming-elasticsearch-2-0 and https://github.com/ruflin/Elastica/issues/1001

## [3.0.1](https://github.com/ruflin/Elastica/compare/3.0.0...3.0.1)

### Improvements
- Update build dependency to elasticsearch 2.1.1 [#1022](https://github.com/ruflin/Elastica/pull/1022)
- Readd \Elastica\Filter\Nested. See https://github.com/ruflin/Elastica/issues/1001 [#1020](https://github.com/ruflin/Elastica/pull/1020)


## [3.0.0](https://github.com/ruflin/Elastica/compare/3.0.0-beta1...3.0.0)

### Backward Compatibility Breaks
- Revert getError changes in Response object and make it better BC compatible. See comment [here](https://github.com/ruflin/Elastica/commit/41a7a2075837320bc9bd3bca4150e05a1ec9a115#commitcomment-15136374).

### Bugfixes
- Function score query: corrected the `score_method` `average` to `avg` [#975](https://github.com/ruflin/Elastica/pull/975)
- Set `json_decode()` assoc parameter to true in `Elastica\Response` [#1005](https://github.com/ruflin/Elastica/pull/1005)
- Add `bigintConversion` to keys passed to connection config in `Elastica\Client` [#1005](https://github.com/ruflin/Elastica/pull/1005)
- Use POST instead of PUT to send bulk requests [#1010](https://github.com/ruflin/Elastica/issues/1010)

### Added
- Elastica\Query\MultiMatch::setFuzziness now supports being set to `AUTO` with the const `MultiMatch::FUZZINESS_AUTO`
- Elastica\Type\Mapping::send now accepts query string parameters to send along with the mapping request
- Elastica\Query\BoolQuery::addFilter

### Improvements
- More info on Elastica\Exception\PartialShardFailureException. Not just number of failed shards.
- Allow bool in TopHits::setSource function [#1012](https://github.com/ruflin/Elastica/issues/1012)

### Deprecated
- Elastica\Query\Filtered triggers E_USER_DEPRECATED error because filtered query is deprecated since ES 2.0.0-beta1. Use BoolQuery instead.
- Elastica\QueryBuilder\DSL\Query::filtered() triggers E_USER_DEPRECATED error because filtered query is deprecated since ES 2.0.0-beta1. Use bool() instead.



## [3.0.0-beta1](https://github.com/ruflin/Elastica/compare/2.3.1...3.0.0-beta1)

### Backward Compatibility Breaks
- Elastica\AbstractUpdateAction::setPercolate now throw DeprecatedException, user Percolator instead
- Elastica\AbstractUpdateAction::getPercolate now throw DeprecatedException, user Percolator instead
- Elastica\AbstractUpdateAction::hasPercolate now throw DeprecatedException, user Percolator instead
- Elastica\Type::delete now throw DeprecatedException, it is no longer possible to delete the mapping for a type. Instead you should delete the index and recreate it with the new mappings
- MoreLikeThis::setLikeText deprecated from ES 2.0, use setLike instead, but there is a difference - setLike haven't trim magic inside for strings
- Elastica\Document, methods: setScript, getScript, hasScript now throw DeprecatedException.
- MoreLikeThis, methods: setLikeText, setIds, setPercentTermsToMatch now throw DeprecatedException.
- Elastica\Aggregation\DateHistogram, methods: setPreZone, setPostZone, setPreZoneAdjustLargeInterval, setPreOffset, setPostOffset now throw DeprecatedException.
- Elastica\Query\Builder trigger E_USER_DEPRECATED error when you try use it.
- Elastica\Filter\Bool and Elastica\Query\Bool trigger E_USER_DEPRECATED error when you try use them.
- Elastica\Query\Fuzzy:addField method trigger E_USER_DEPRECATED error
- Elastica\Query\FunctionScore:addBoostFactorFunction method trigger E_USER_DEPRECATED error
- Elastica\Query:setLimit method trigger E_USER_DEPRECATED error
- Elastica\Document:add method trigger E_USER_DEPRECATED error
- Type::moreLikeThis API was removed from ES 2.0, use MoreLikeThis query instead
- Remove Thrift transport and everything related to it
- Remove Memcache transport and everything related to it
- Remove BulkUdp and everything related to it
- Remove Facets and everything related to it
- Remove ansible scripts for tests setup and Vagrantfile as not needed anymore.
  All is based on docker containers now
- Support for PHP 5.3 removed
- Elastica\Reponse::getError() now returns and array instead of a string
- Move function \Elastica\Index\Status::getAliases() and hasAlias(...) to \Elastica\Index::getAliases()
- Remove \Elastica\Index\Status object and related functions
- \Elastica\Query\FuzzyLikeThis remove as not supported anymore
- Remove \Elastica\Status::getServerStatus() as the information was removed
- DeleteByQuery now requires the delete-by-query plugin isntalled
- Remove \Elastica\Filter\Nested as it is replaced by \Elastica\Query\Nested
- Require at least PHP 5.4

### Bugfixes
- Fixed GeoShapeProvided relation parameter position

### Added
- Elastica\Reponse::getErrorMessage was added as getError is now an object
- Elastica\Query\MoreLikeThis::setLike
- \Elastica\Exception\DeprecatedException
- Connection option to convert JSON bigint results to strings can now be set [#717](https://github.com/ruflin/Elastica/issues/717)

### Improvements
- Travis builds were moved to docker-compose setup. Ansible scripts and Vagrant files were removed
- trigger_error with E_USER_DEPRECATE added to deprecated places
- DeprecatedException will be thrown, if there is a call of method that not support BC

### Deprecated
- Elastica\Type::delete is deprecated
- Elastica\Filter\Bool is deprecated
- Elastica\Query\Bool is deprecated
- Elastica\Query\MoreLikeThis::setLikeText is deprecated
- Elastica\Query\MoreLikeThis::setIds is deprecated

## [2.3.1](https://github.com/ruflin/Elastica/releases/tag/2.3.1) - 2015-10-17

### Bugfixes
- Filters aggregation: empty name is named bucket [#935](https://github.com/ruflin/Elastica/pull/935)
- Prevent mix keys in filters ([#936](https://github.com/ruflin/Elastica/pull/936)) [#939](https://github.com/ruflin/Elastica/pull/939)
- Fix empty string is not anonymous filter [#935](https://github.com/ruflin/Elastica/pull/935)
- Filters aggregation: empty name is named bucket [#935](https://github.com/ruflin/Elastica/pull/935)

### Added
- Support for field_value_factor [#953](https://github.com/ruflin/Elastica/pull/953)
- Added setMinDocCount and setExtendedBounds options [#947](https://github.com/ruflin/Elastica/pull/947)
- Avoid environment dependecies in tests [#938](https://github.com/ruflin/Elastica/pull/938)

### Improvements
- Update elasticsearch dependency to elasticsearch 1.7.3 [#957](https://github.com/ruflin/Elastica/pull/957)

### Deprecated
- Added exceptions of deprecated transports to deprecation list


## [2.3.0](https://github.com/ruflin/Elastica/releases/tag/2.3.0) - 2015-09-15


### Backward Compatibility Breaks
- Objects do not casts to arrays in setters and saved in params as objects. There is many side effects if
  you work with params on "low-level" or change your objects after you call setter with object
  as argument. [#916](https://github.com/ruflin/Elastica/pull/916)

### Added
- Add Script File feature [#902](https://github.com/ruflin/Elastica/pull/902) [#914](https://github.com/ruflin/Elastica/pull/914)

### Improvements
- Support the http.compression in the Http transport adapter [#515](https://github.com/ruflin/Elastica/issues/515)
- Introduction of Lazy toArray [#916](https://github.com/ruflin/Elastica/pull/916)
- Update Elasticsearch dependency to 1.7.2 [#929](https://github.com/ruflin/Elastica/pull/929)



## [2.2.1](https://github.com/ruflin/Elastica/releases/tag/2.2.1) - 2015-08-10


### Added
- Support for index template added [#905](https://github.com/ruflin/Elastica/pull/905)

### Improvements
- Update Elasticsearch dependency to 1.7.1 and update plugin dependencies [#909](https://github.com/ruflin/Elastica/pull/909)
- Update php-cs-fixer to 1.10 [#898](https://github.com/ruflin/Elastica/pull/898)
- Elastica\QueryBuilder now uses Elastica\QueryBuilder\Version\Latest as default version to avoid empty version classes. [#897](https://github.com/ruflin/Elastica/pull/897)
- Update elasticsearch-image to work with ES 1.7.1 [#907](https://github.com/ruflin/Elastica/pull/907)
- Local dev environment was refactored to fully work in docker environment. Running tests is now only one command: `make tests` [#901](https://github.com/ruflin/Elastica/pull/901)

### Deprecated
- Elastica\QueryBuilder\Version\Version150 deprecated in favor of Elastica\QueryBuilder\Version\Latest [#897](https://github.com/ruflin/Elastica/pull/897)


## [2.2.0](https://github.com/ruflin/Elastica/releases/tag/2.2.0) - 2015-07-08


### Backward Compatibility Breaks
- Usage of constant DEBUG and method Elastica\Util::debugEnabled is removed. [#868](https://github.com/ruflin/Elastica/pull/868)
- Elastica\Response::getTransferInfo will not return "request_header" by default. [#868](https://github.com/ruflin/Elastica/pull/868)
- The Image Plugin is currently not compatible with Elasticearch 1.6.0

### Bugfixes
- Fixed segmentation fault in PHP7 [#868](https://github.com/ruflin/Elastica/pull/868)
- Removed deprecation for Elastica\Type::deleteByQuery [#875](https://github.com/ruflin/Elastica/pull/875)

### Improvements
- `CallbackStrategy` now will accept any `callable` as callback, not only instance of `Closure`. [#871](https://github.com/ruflin/Elastica/pull/871)
- `StrategyFactory` now will try to find predefined strategy before looking to global namespace. [#877](https://github.com/ruflin/Elastica/pull/877)
- Update elasticsearch dependency to elasticsearch 1.6.0 https://www.elastic.co/downloads/past-releases/elasticsearch-1-6-0
- All elasticsearch plugin dependencies were updated to the newest version.
- Methods of classes in `QueryBuilder\DSL` namespace now have exact same signatures as corresponding constructors. [#878](https://github.com/ruflin/Elastica/pull/878)
- Constructor of `Aggregation\Filter` now accepts filter as second parameter [#878](https://github.com/ruflin/Elastica/pull/878)
- Constructor of `Filter\AbstractMulti` (`BoolAnd`, `BooldOr`) now accepts array of filters as parameter [#878](https://github.com/ruflin/Elastica/pull/878)
- Constructor of `Query\Match` now accepts arguments [#878](https://github.com/ruflin/Elastica/pull/878)
- Coverage Reporting improved with Codecov [#888](https://github.com/ruflin/Elastica/pull/888)
- Added 'query_cache' option for search [#886](https://github.com/ruflin/Elastica/pull/886)

## [2.1.0](https://github.com/ruflin/Elastica/releases/tag/2.1.0) - 2015-06-01

### Added
- Multiple rescore query [#820](https://github.com/ruflin/Elastica/issues/820/)
- Support for a custom connection timeout through a connectTimeout parameter. [#841](https://github.com/ruflin/Elastica/issues/841/)
- SignificantTerms Aggregation [#847](https://github.com/ruflin/Elastica/issues/847/)
- Support for 'precision_threshold' and 'rehash' options for the Cardinality Aggregation [#851]
- Support for retrieving id node [#852](https://github.com/ruflin/Elastica/pull/852)
- Scroll Iterator [#842](https://github.com/ruflin/Elastica/issues/842/)
- Gitter Elastica Chat Room add for Elastica discussions: https://gitter.im/ruflin/Elastica
- Introduce PHP7 compatibility and tests. [#837](https://github.com/ruflin/Elastica/pull/837)
- `Tool\CrossIndex` for reindexing and copying data and mapping between indices [#853](https://github.com/ruflin/Elastica/pull/853)
- CONTIRUBTING.md file added for contributor guidelines. [#854](https://github.com/ruflin/Elastica/pull/854)

### Improvements
- Introduction of Changelog standard based on http://keepachangelog.com/. changes.txt moved to CHANGELOG.md [#844](https://github.com/ruflin/Elastica/issues/844/)
- Make host for all tests dynamic to prepare it for a more dynamic test environment [#846](https://github.com/ruflin/Elastica/pull/846)
- Node information is retrieved based on id instead of name as multiple nodes can have the same name. [#852](https://github.com/ruflin/Elastica/pull/852)
- Guzzle Http dependency updated to 5.3.*
- Remove NO_DEV builds from travis build matrix to speed up building. All builds include no dev packages.
- Introduction of benchmark test group to make it easy to run benchmark tests.
- Make the docker images directly [available](https://hub.docker.com/u/ruflin/) on the docker registry. This speeds up fetching of the images and automates the build of the images.

### Backward Compatibility Breaks
- `Elastica\ScanAndScroll::$_lastScrollId` removed: `key()` now always returns the next scroll id [#842](https://github.com/ruflin/Elastica/issues/842/)


### Deprecated
- Facets are deprecated. You are encouraged to migrate to aggregations instead. [#855](https://github.com/ruflin/Elastica/pull/855/)
- Elastica\Query\Builder is deprecated. Use new Elastica\QueryBuilder instead. [#855](https://github.com/ruflin/Elastica/pull/855/)
- For PHP 7 compatibility Elastica\Query\Bool was renamed to *\BoolQuery, Elastica\Filter\Bool was renamed to BoolFilter, Elastica\Transport\Null was renamed to NullTransport as Null and Bool are reserved phrases in PHP 7. Proxy objects for all three exist to keep backward compatibility. It is recommended to start using the new objects as the proxy classes will be deprecated as soon as PHP 7 is stable. [#837](https://github.com/ruflin/Elastica/pull/837)



## [2.0.0](https://github.com/ruflin/Elastica/releases/tag/2.0.0) - 2015-05-11


### Backward Compatibility Breaks
- Elastica\Query\QueryString::setLowercaseExpandedTerms removed [#813](https://github.com/ruflin/Elastica/issues/813/)
- Update elasticsearch dependency to elasticsearch 1.5.2 https://www.elastic.co/downloads/past-releases/elasticsearch-1-5-2 [#834](https://github.com/ruflin/Elastica/issues/834/)
- Added deprecation notice to Elastica\Transport\Thrift, Elastica\Transport\Memcached and Elastica\Type::deleteByQuery  [#817](https://github.com/ruflin/Elastica/issues/817/)
- Escape new symbols in Util::escapeTerm [#795](https://github.com/ruflin/Elastica/issues/795/)

### Bugfixes
- Fix empty bool query to act as match all query [#817](https://github.com/ruflin/Elastica/issues/817/)
- Fixed short match construction in query DSL [#796](https://github.com/ruflin/Elastica/issues/796/)
- Index optimize method to return Response object. [#797](https://github.com/ruflin/Elastica/issues/797/)
- Fix fluent interface inconsistency [#788](https://github.com/ruflin/Elastica/issues/788/)


### Improvements
- Add testing on PHP 7 on Travis [#826](https://github.com/ruflin/Elastica/issues/826/)
- Allow bool in Query::setSource function [#818](https://github.com/ruflin/Elastica/issues/818/) https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-source-filtering.html
- deleteByQuery() implemented in Elastica\Index [#816](https://github.com/ruflin/Elastica/issues/816/)
- Add MLT query against documents [#814](https://github.com/ruflin/Elastica/issues/814/)
- Added Elastica\Query\SimpleQueryString::setMinimumShouldMatch [#813](https://github.com/ruflin/Elastica/issues/813/)
- Added Elastica\Query\FunctionScore::setMinScore [#813](https://github.com/ruflin/Elastica/issues/813/)
- Added Elastica\Query\MoreLikeThis::setMinimumShouldMatch [#813](https://github.com/ruflin/Elastica/issues/813/)
- Added new methods to Elastica\Aggregation\DateHistogram: setOffset, setTimezone [#813](https://github.com/ruflin/Elastica/issues/813/)
- Following methods in Elastica\Aggregation\DateHistogram marked as deprecated: setPreOffset, setPostOffset, setPreZone, setPostZone, setPreZoneAdjustLargeInterval [#813](https://github.com/ruflin/Elastica/issues/813/)
- Add Elastica\Facet\DateHistogram::setFactor() [#806](https://github.com/ruflin/Elastica/issues/806/)
- Added Elastica\Query\QueryString::setTimezone [#813](https://github.com/ruflin/Elastica/issues/813/)
- Add .editorconfig [#807](https://github.com/ruflin/Elastica/issues/807/)
- Added Elastica\Suggest\Completion [#808](https://github.com/ruflin/Elastica/issues/808/)
- Fix elasticsearch links to elastic domain [#809](https://github.com/ruflin/Elastica/issues/809/)
- Added Elastica\Query\Image [#787](https://github.com/ruflin/Elastica/issues/787/)
- Add Scrutinizer Code Quality status badge
- Added support for percentiles aggregation [#786](https://github.com/ruflin/Elastica/issues/786/)



## Changelog before 2.0.0
The changelog before version 2.0.0 was organised by date. All changes can be found below.

2015-02-17
- Release v1.4.3.0
- Added Elastica\Query\MatchPhrase [#599](https://github.com/ruflin/Elastica/issues/599/)
- Added Elastica\Query\MatchPhrasePrefix [#599](https://github.com/ruflin/Elastica/issues/599/)

2015-02-04
- Reset PHP 5.3 tests and enable compatibility for PHP 5.3 again

2015-02-16
- Update elasticsearch compatibility to 1.4.3 [#782](https://github.com/ruflin/Elastica/issues/782/)
- Add support for scripted metric aggrations [#780](https://github.com/ruflin/Elastica/issues/780/)

2015-02-14
- Added availability to specify regexp options in \Elastica\Filters\Regexp [#583](https://github.com/ruflin/Elastica/issues/583/) [#777](https://github.com/ruflin/Elastica/issues/777/)
- Add HHVM as build in travis [#649](https://github.com/ruflin/Elastica/issues/649/)

2015-02-11
- Fixed issue with OutOfMemory exception in travis builds [#775](https://github.com/ruflin/Elastica/issues/775/)
- Add support for filters aggregation [#773](https://github.com/ruflin/Elastica/issues/773/)

2015-01-27
- Housekeeping, coding standard [#764](https://github.com/ruflin/Elastica/issues/764/)
- Exception\ElasticsearchException now can be catched like all other exceptions as Exception\ExceptionInterface [#762](https://github.com/ruflin/Elastica/issues/762/)

2015-01-25
- Release v1.4.2.0

2015-01-23
- Added Elastica\Query\Regexp [#757](https://github.com/ruflin/Elastica/issues/757/)

2015-01-19
- Update to elasticsearch 1.4.2 [#378](https://github.com/ruflin/Elastica/issues/378/)
- Remove support for PHP 5.3

2015-01-14
- added @return annotation to top_hits aggregation DSL method [#752](https://github.com/ruflin/Elastica/issues/752/)

2015-01-07
- Added Elastica\Aggregation\TopHits [#718](https://github.com/ruflin/Elastica/issues/718/)

2015-01-05
- Vagrantfile updated [#742](https://github.com/ruflin/Elastica/issues/742/)
- Plugins updated to ES 1.3.4
- Since new version of thrift plugin is compatible with ES 1.3.4, plugin added back to test environment

2014-12-30
- Added: Filter\Range::setExecution, Filter\Terms::setExecution, Filter\Missing::setExistence, Filter\Missing::setNullValue, Filter\HasChild::setMinumumChildrenCount, Filter\HasChild::Filter\HasChild::setMaximumChildrenCount, Filter\Indices::addIndex
- Filter\HasChild::setType, Filter\HasParent::setType now support Type instance as argument
- Filter\Indices::setIndices, Filter\Indices::addIndex now support Index instance as argument
- (BC break) Removed as added by mistake: Filter\HasChild::setScope, Filter\HasParent::setScope, Filter\Nested::setScoreMode, Filter\Bool::setBoost

2014-12-23
- Additional Request Body Options for Percolator [#737](https://github.com/ruflin/Elastica/issues/737/)

2014-12-19
- making sure id is urlencoded when using updateDocument [#734](https://github.com/ruflin/Elastica/issues/734/)
- Implement the `weight` in the function score query [#735](https://github.com/ruflin/Elastica/issues/735/)

2014-12-09
- Changed setRealWorldErrorLikelihood to setRealWordErrorLikelihood [#729](https://github.com/ruflin/Elastica/issues/729/)

2014-11-23
- allow to customize the key on a range aggregation [#728](https://github.com/ruflin/Elastica/issues/728/)

2014-12-14
- Added fluent interface to Elastica\Query::setRescore [#733](https://github.com/ruflin/Elastica/issues/733/)

2014-11-30
- Added transport to support egeloen/http-adapter [#727](https://github.com/ruflin/Elastica/issues/727/)

2014-11-20
- add cache control parameters support to Elastica\Filter\Bool [#725](https://github.com/ruflin/Elastica/issues/725/)

2014-11-19
- Avoid remove previously added params when adding a suggest to the query [#726](https://github.com/ruflin/Elastica/issues/726/)

2014-11-16
- Added Elastica\QueryBuilder [#724](https://github.com/ruflin/Elastica/issues/724/)
- Update to elasticsearch 1.4.0
- Disable official support for PHP 5.3

2014-11-13
- fixed reserved words in queries which composed of upper case letters (Util::replaceBooleanWords) [#722](https://github.com/ruflin/Elastica/issues/722/)

2014-10-31
- Adding PSR-4 autoloading support [#714](https://github.com/ruflin/Elastica/issues/714/)

2014-10-29
- Updated Type::getDocument() exception handling. \Elastica\Exception\ResponseException will be thrown instead of \Elastica\Exception\NotFoundException if the ES response contains any error (i.e: Missing index) (BC break) [#687](https://github.com/ruflin/Elastica/issues/687/)

2014-10-22
- Added Util::convertDateTimeObject to Util class to easily convert \DateTime objects to required format [#709](https://github.com/ruflin/Elastica/issues/709/)

2014-10-15
- Remove ResponseException catch in Type::getDocument() [#704](https://github.com/ruflin/Elastica/issues/704/)

2014-10-10
- Fixed Response::isOk() to work better with bulk update api [#702](https://github.com/ruflin/Elastica/issues/702/)
- Adding magic __call() [#700](https://github.com/ruflin/Elastica/issues/700/)

2014-10-05
- ResultSet creation moved to static ResultSet::create() method [#690](https://github.com/ruflin/Elastica/issues/690/)
- Accept an options array at Type::updateDocument() [#686](https://github.com/ruflin/Elastica/issues/686/)
- Improve exception handling in Type::getDocument() [#693](https://github.com/ruflin/Elastica/issues/693/)

2014-10-04
- Release v1.3.4.0
- Update to elasticsearch 1.3.4 [#691](https://github.com/ruflin/Elastica/issues/691/)

2014-09-22
- Update the branch alias in composer.json to match the library version [#683](https://github.com/ruflin/Elastica/issues/683/)

2014-09-16
- Update license in composer.json to match project [#681](https://github.com/ruflin/Elastica/issues/681/)

2014-09-08
- Delete execution permission from non-executable files [#677](https://github.com/ruflin/Elastica/issues/677/)

2014-08-25
- Top-level filter parameter in search has been renamed to post_filter [#669](https://github.com/ruflin/Elastica/issues/669/) [#670](https://github.com/ruflin/Elastica/issues/670/)
- Deprecated: Elastica\Query::setFilter() is deprecated. Use Elastica\Query::setPostFilter() instead. [#669](https://github.com/ruflin/Elastica/issues/669/)
- Deprecated: Elastica\Query::setPostFilter() passing filter as array is deprecated. Pass instance of AbstractFilter instead. [#669](https://github.com/ruflin/Elastica/issues/669/)

2014-08-22
- Fixed escaping of / character in Elastica\Util::escapeTerm(), removed usage of JSON_UNESCAPED_SLASHES in Elastica\JSON [#660](https://github.com/ruflin/Elastica/issues/660/)

2014-08-06
- Add connection pool and connection strategy

2014-07-26
- Release v1.3.0.0
- Prepare Elastica Release v1.3.0.0

2014-07-25
- Update to elasticsearch version 1.3.0 https://www.elastic.co/downloads/past-releases/1-3-0

2014-07-14
 - Add setQuery() method to Elastica\Query\ConstantScore [#653](https://github.com/ruflin/Elastica/issues/653/)

2014-07-12
 - Be able to configure ES host/port via ENV var in test env [#652](https://github.com/ruflin/Elastica/issues/652/)

2014-07-07
 - Fix FunstionScore Query random_score without seed bug. [#647](https://github.com/ruflin/Elastica/issues/647/)

2014-07-02
- Add setPostFilter method to Elastica\Query (https://www.elastic.co/guide/en/elasticsearch/guide/current/_post_filter.html) [#645](https://github.com/ruflin/Elastica/issues/645/)

2014-06-30
- Add Reverse Nested aggregation (https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html). [#642](https://github.com/ruflin/Elastica/issues/642/)

2014-06-14
- Release v1.2.1.0
- Removed the requirement to set arguments filter and/or query in Filtered, according to the documentation: https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-filtered-query.html [#616](https://github.com/ruflin/Elastica/issues/616/)

2014-06-13
- Stop ClientTest->testDeleteIdsIdxStringTypeString from failing 1/3 of the time [#634](https://github.com/ruflin/Elastica/issues/634/)
- Stop ScanAndScrollTest->testQuerySizeOverride from failing frequently for no reason [#635](https://github.com/ruflin/Elastica/issues/635/)
- rework Document and Script so they can share some infrastructure allowing scripts to specify things like _retry_on_conflict and _routing [#629](https://github.com/ruflin/Elastica/issues/629/)

2014-06-11
- Allow bulk API deletes to be routed [#631](https://github.com/ruflin/Elastica/issues/631/)

2014-06-10
- Update travis to elasticsearch 1.2.1, disable Thrift plugin as not compatible and fix incompatible tests

2014-06-04
- Implement Boosting Query (https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html) [#625](https://github.com/ruflin/Elastica/issues/625/)

2014-06-02
- add retry_on_conflict support to bulk [#623](https://github.com/ruflin/Elastica/issues/623/)

2014-06-01
- toString updated to consider doc_as_upsert if sent an array source [#622](https://github.com/ruflin/Elastica/issues/622/)

2014-05-27
- Fix Aggragations/Filter to work with es v1.2.0 [#619](https://github.com/ruflin/Elastica/issues/619/)

2014-05-25
- Added Guzzle transport as an alternative to the default Http transport [#618](https://github.com/ruflin/Elastica/issues/618/)
- Added Elastica\ScanAndScroll Iterator (https://www.elastic.co/guide/en/elasticsearch/guide/current/scan-scroll.html) [#617](https://github.com/ruflin/Elastica/issues/617/)

2014-05-13
- Add JSON compat library; Elasticsearch JSON flags and nicer error handling [#614](https://github.com/ruflin/Elastica/issues/614/)

2014-05-12
- Update dev builds to phpunit 4.1.*

2014-05-11
- Set processIsolation and backupGlobals to false to speed up tests. processIsolation was very slow with phpunit 4.0.19.

2014-05-05
- Fix get settings on alaised index [#608](https://github.com/ruflin/Elastica/issues/608/)
- Added named function for source filtering [#605](https://github.com/ruflin/Elastica/issues/605/)
- Scroll type constant to Elastica\Search added [#607](https://github.com/ruflin/Elastica/issues/607/)

2014-04-28
- Added setAnalyzer method to Query\FuzzyLikeThis Class and fixed issue with params not being merged [#611](https://github.com/ruflin/Elastica/issues/611/)
- Typo fixes [#600](https://github.com/ruflin/Elastica/issues/600/), [#602](https://github.com/ruflin/Elastica/issues/602/)
- Remove unreachable return statement [#598](https://github.com/ruflin/Elastica/issues/598/)

2014-04-27
- Release v1.1.1.1
- Fix missing use in TermsStats->setOrder() [#597](https://github.com/ruflin/Elastica/issues/597/)
- Replace all instances of ElasticSearch with Elasticsearch [#597](https://github.com/ruflin/Elastica/issues/597/)

2014-04-24
- Fixing the Bool filter with Bool filter children bug [#594](https://github.com/ruflin/Elastica/issues/594/)

2014-04-22
- Remove useless echo in the Memcache Transport object [#595](https://github.com/ruflin/Elastica/issues/595/)

2014-04-21
- escape \ by \\ [#592](https://github.com/ruflin/Elastica/issues/592/)

2014-04-20
- Handling of HasChild type parsing bug [#585](https://github.com/ruflin/Elastica/issues/585/)
- Consolidate Index getMapping tests [#591](https://github.com/ruflin/Elastica/issues/591/)
- Fix Type::getMapping when using an aliased index [#588](https://github.com/ruflin/Elastica/issues/588/)

2014-04-19
- Release v1.1.1.0
- Update to elasticsearch 1.1.1 https://www.elastic.co/downloads/past-releases/1-1-1
- Remove CustomFiltersScore and CustomScore query as removed in elasticsearch 1.1.0 https://github.com/elasticsearch/elasticsearch/pull/5076/files
- Update Node Info to use plugins instead of plugin (https://github.com/elasticsearch/elasticsearch/pull/5072)
- Fix mapping issue for aliases [#588](https://github.com/ruflin/Elastica/issues/588/)

2014-04-17
- Only trap real JSON parse errors in Response class [#586](https://github.com/ruflin/Elastica/issues/586/)

2014-04-09
- Added Cardinality aggregation [#581](https://github.com/ruflin/Elastica/issues/581/)

2014-04-07
- Support for Terms filter lookup options [#579](https://github.com/ruflin/Elastica/issues/579/)

2014-03-29
- Update to elasticsearch 1.1.0 https://www.elastic.co/downloads/past-releases/1-1-0

2014-03-26
- Fixed Query\Match Fuzziness parameter type [#576](https://github.com/ruflin/Elastica/issues/576/)

2014-03-24
- Release v1.0.1.2
- Added Filter\Indices [#574](https://github.com/ruflin/Elastica/issues/574/)

2014-03-25
- Allow json string as data srouce for Bulk\Action on update [#575](https://github.com/ruflin/Elastica/issues/575/)

2014-03-20
- Allow for request params in delete by query calls [#573](https://github.com/ruflin/Elastica/issues/573/)

2014-03-17
- Added filters: AbstractGeoShape, GeoShapePreIndexed, GeoShapeProvided [#568](https://github.com/ruflin/Elastica/issues/568/)

2014-03-15
- Percolate existing documents and add percolate options ([#570](https://github.com/ruflin/Elastica/issues/570/))

2014-03-14
- Added Query\Rescore [#441](https://github.com/ruflin/Elastica/issues/441/)

2014-03-13
- Added missing query options for MultiMatch (operator, minimum_should_match, zero_terms_query, cutoff_frequency, type, fuzziness, prefix_length, max_expansions, analyzer) [#569](https://github.com/ruflin/Elastica/issues/569/)
- Added missing query options for Match (zero_terms_query, cutoff_frequency) [#569](https://github.com/ruflin/Elastica/issues/569/)

2014-03-11
- Fixed request body reuse in http transport [#567](https://github.com/ruflin/Elastica/issues/567/)

2014-03-08
- Release v1.0.1.1
- Enable goecluster-facet again as now compatible with elasticsearch 1.0 on travis
- Run elasticsearch in the background to not have log output in travis build
- Set memache php version as environment variable
- Update to memcache 3.0.8 for travis

2014-03-07
- Add snapshot / restore functionality (Elastica\Snapshot) [#566](https://github.com/ruflin/Elastica/issues/566/)

2014-03-04
- Add PHP 5.6 to travis test environment
- Improve performance of Elastica/Status->getIndicesWithAlias and aliasExists on clusters with many indices [#563](https://github.com/ruflin/Elastica/issues/563/)

2014-03-02
- Release v1.0.1.0
- Fixed Type->deleteByQuery() not working with Query objects [#554](https://github.com/ruflin/Elastica/issues/554/)

2014-02-27
- Update to elasticsearch 1.0.1. Update Thrift and Geocluster plugin.

2014-02-25
- Add JSON_UNESCAPED_UNICODE and JSON_UNESCAPED_SLASHES options in Elastica/Transport/Http, Elastica/Bulk/Action [#559](https://github.com/ruflin/Elastica/issues/559/)

2014-02-20
- Fixed unregister percolator (still used _percolator instead of .percolator). removed duplicate slash from register percolator route. [#558](https://github.com/ruflin/Elastica/issues/558/)

2014-02-17
- Throw PartialShardFailureException if response has failed shards
- Elastica/Aggregations/GlobalAggragation not allowed as sub aggragation [#555](https://github.com/ruflin/Elastica/issues/555/)

2014-02-14
- Add methods setSize, setShardSize to Elastica/Aggregation/Terms
- Elastica/Aggregation/GlobalAggregationTest fixed bug where JSON returned [] instead of {}
- Elastica/ResultSet added method hasAggregations

2014-02-13
- Moved from Apache License to MIT license

2014-02-12
- Release v1.0.0.0
- Updated to elasticsearch 1.0: https://www.elastic.co/blog/1-0-0-released/

2014-02-11
- Add aggregations

2014-02-08
- Setting shard timeout doesn't work [#547](https://github.com/ruflin/Elastica/issues/547/)

2014-02-04
- Remove Elastica\Query\Field and Elastica\Query\Text, which are not supported in ES 1.0.0.RC1
- Minor tweaking of request and result handling classes to adjust for changes in ES 1.0.0.RC1
- Update mapper-attachments plugin to version 2.0.0.RC1 in .travis.yml
- Adjust tests to account for changes in ES 1.0.0.RC1
- Prevent the geocluster-facet plugin from being installed in test/bin/run_elasticsearch.sh as the plugin has not yet been updated for ES 1.0.0.RC1

2014-01-06
- Update to elasticsearch v1.0.0.RC2

2014-01-02
- Added Elastica\Query\DisMax
- Update to elasticsearch v1.0.0.RC1

2014-01-02
- Release v0.90.10

2014-01-31
- Fix _bulk delete proxy methods if type or index not explicitly defined.

2014-01-28
- Add _bulk delete proxy methods to Index and Type for consistency.
- Use the HTTP response code of GET requests (getDocument), instead of extists/found json property.

2014-01-22
- Add getParam & getProperties methods to Elastica\Type\Mapping

2014-01-21
- Code coverage generation for coveralls.io added: https://coveralls.io/r/ruflin/Elastica
- Add support for shard timeout to the Bulk api.

2014-01-17
- Fix typo in constant name: Elastica\Query\FunctionScore::DECAY_GUASS becomes DECAY_GAUSS

2014-01-13
- Add support for _bulk update

2014-01-14
- added \Elastica\Exception\ResponseException::getElasticsearchException()
- Changed logger default log level to debug from info

2014-01-13
- Update to elasticsearch 0.90.10
- Add Elastica\Facet\TermsStats::setOrder()

2014-01-08
- Adding analyze function to Index to expose the _analyze API

2014-01-07
- Document::setDocAsUpsert() now returns the Document

2013-12-18
- Update to Elasticsearch 0.90.8
- Add support for simple_query_string query

2013-12-15
- Add support for filter inside HasChild filter
- Add support for filter inside HasParent filter

2013-12-12
- Always send scroll_id via HTTP body instead of as a query param
- Fix the manner in which suggestion results are returned in \Elastica\ResultSet and adjust associated tests to account for the fix.
- Add \Elastica\Resultset::hasSuggests()

2013-12-11
- Pass arguments to optimize as query
- Add refreshAll on Client

2013-12-07
- Added Result::hasFields() and Result::hasParam() methods for consistency with Document

2013-12-07
- Escape slash in Util::escapeTerm, as it is used for regexp from Elastic 0.90

2013-12-05
- Add *.iml to .gitignore
- Refactor suggest implementation (\Elastica\Suggest, \Elastica\Suggest\AbstractSuggest, and \Elastica\Suggest\Term) to more closely resemble query implementation. (BC break)
- \Elastica\Search::addSuggest() has been renamed to \Elastica\Search::setSuggest()
- \Elastica\Query::addSuggest() has been renamed to \Elastica\Query::setSuggest()
- Add \Elastica\Suggest\Phrase, \Elastica\Suggest\CandidateGenerator\AbstractCandidateGenerator, and \Elastica\Suggest\CandidateGenerator\DirectGenerator
  (see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html)

2013-12-04
- Remove boost from FunctionScore::addFunction because this is not supported by elasticsearch

2013-12-02
- Issue [#491](https://github.com/ruflin/Elastica/issues/491/) resolved

2013-12-01
- Issue [#501](https://github.com/ruflin/Elastica/issues/501/) resolved
- satooshi/php-coveralls package added for coverall.io
- Multiple badges for downloads and latest stable release added

2013-11-30
- Remove facets param from query if is empty array
- Add size param to API for TermsStats

2013-11-23
- Release v0.90.7.0

2013-11-19
- Updated geocluster-facet to 0.0.9

2013-11-18
- Added \Elastica\Filter\Regexp

2013-11-16
- Remove wrong documentation for "no limit" [#496](https://github.com/ruflin/Elastica/issues/496/)
- Update to elasticsearch 0.90.7

2013-11-03
- Issue [#490](https://github.com/ruflin/Elastica/issues/490/): Set Elastica\Query\FunctionScore::DECAY_EXPONENTIAL to "exp" instead of "exponential"

2013-10-29
- Elastica_Type::exists() added
  See https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-types-exists.html#indices-types-exists

2013-10-27
- Adapted possible values (not only in) for minimum_should_match param based on elasticsearch documetnation https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html

2013-10-27
- Release v0.90.5.0

2013-10-26
- Update to elasticsearch 0.90.5

2013-10-21
- Fix \Elastica\Filter\HasParent usage of \Elastica\Query as to not collide with \Elastica\Filter\Query, bring \Elasitca\Filter\HasChild into line

2013-10-01
- Also pass the current client object to the failure callback in \Elastica\Client.

2013-09-20
- Update to geocluster-facet 0.0.8
- Add support for term suggest API
  See https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-term.html

2013-09-18
- Fix \Elastica\Filter\HasChild usage of \Elastica\Query as to not collide with \Elastica\Filter\Query namespace

2013-09-17
- Update to elasticsearch 0.90.4
- Add support for function_score query
- Skip geocluster-facet test if the plugin is not installed
- Correct \Elastica\Test\ClientTest to catch the proper exception type on connection failure
- Fix unit test errors

2013-09-14
- Nested filter supports now the setFilter method

2013-09-03
- Support isset() calls on Result objects

2013-08-27
- Add \ArrayAccess on the ResultSet object

2013-08-25
- Update to elasticsearch 0.90.3

2013-08-25
- Release v0.90.2.0

2013-08-20
- Support for "proxy" param for http connections

2013-08-17
- Add support for fields parameter in Elastica_Type::getDocument()

2013-08-13
- Add a getQuery method on the FilteredQuery object

2013-08-01
- Second param to \Elastica\Search.php:count($query = '', $fullResult = false) added. If second param is set to true, full ResultSet is returned including facets.

2013-07-16
- Plugin geocluster-facet support added

2013-07-02
- Add Query\Common
- Can now create a query by passing an array to Type::search()

2013-07-01
- Add Filter\GeohashCell

2013-06-30
- Revamped upsert so that Scripts are now first class citizens. (BC break)
  See http://elastica.io/migration/0.90.2/upsert.html
- Implemented doc_as_upsert.

2013-06-29
- Update to elasticsearch 0.90.2
- Enabled ES_WAIT_ON_MAPPING_CHANGE for travis builds

2013-06-25
- Added upsert support when updating a document with a partial document or a script.

2013-06-23
- Add filtered queries to the percolator API.

2013-06-21
- Correct class name for TermTest unit test
- Implement terms lookup feature for terms filter

2013-06-14
- Fix support for making scroll queries once the scroll has been started.

2013-06-07
- Release 0.90.1.0

2013-06-05
- Changed package name to lowercase to prevent potential issues with case sensitive file systems and to refelect the package name from packagist.org.
  If you are requiring elastica in your project you might want to change the name in the require to lowercase, although it will still work if written in uppercase.
  The composer autoloader will handle the package correctly and you will not notice any difference.
  If you are requiring or including a file by hand with require() or include() from the composer vendor folder, pay attention that the package name in
  the path will change to lowercase.
- Add Bulk\Action\UpdateDocument.
- Update Bulk\Action\AbstractDocument and Bulk\Action to enable use of OP_TYPE_UPDATE.
- Update .travis.yml to use Elasticsearch version 0.9.1, as bulk update is a new feature in 0.9.1.

2013-06-04
- Elastica\Client::_configureParams() changed to _prepareConnectionParams(), which now takes the config array as an argument

2013-06-03
- Add getPlugins and hasPlugin methods to Node\Info

2013-05-30
- Update Index\Status::getAliases() to use new API
- Update Index\Status::getSettings() to use new API

2013-05-29
- Add _meta to mapping. [#330](https://github.com/ruflin/Elastica/issues/330/)

2013-05-27
- Added parameters to implement scroll

2013-05-23
- add support PSR-3(https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
- Elastica\Log implement LoggerInterface(extends Psr\Log\AbstractLogger)
  if you want use logging need install https://github.com/php-fig/log for example (composer require psr/log:dev-master)
  if use Elastica\Log inside Elastica\Client nothing more is needed
  if use Elastica\Log outside we need use as(https://github.com/php-fig/log) for example Elastica\Log::info($message) or Elastica\Log::log(LogLevel::INFO,$message)
- Elastica\Client add setLogger for setting custom Logger for example Monolog(https://github.com/Seldaek/monolog)

2013-05-18
- Elastica\Index::exists fixed for 0.90.0. HEAD request method introduced
- Elastica\Filter\AbstractMulti::getFilters() added
- Implement Elastica\Type\Mapping::enableAllField
- Refresh for Elastica\Index::flush implemented [#316](https://github.com/ruflin/Elastica/issues/316/)
- Added optional parameter to filter result while percolate [#384](https://github.com/ruflin/Elastica/issues/384/)

2013-05-07
- Added EXPERIMENTAL DocumentObjectInterface to be used with Type::addObject()/addObjects()

2013-04-23
- Removed Elastica\Exception\AbstractException
- All Exceptions now implement Elastica\Exception\ExceptionInterface

2013-04-17
- Query\Fuzzy to comply with DSL spec. Multi-field queries now throw an exception. Implemented: Query\Fuzzy::setField, Query\Fuzzy::setFieldOption.
- Query\Fuzzy::addField has been deprecated.

2013-04-12
- Adding max score information in ResultSet
- Adding test for the ResultSet class

2013-03-20
- Removal of unsupported minimum_number_should_match for Boolean Filter

2013-02-25
- Added Elastica\Bulk class responsible for performing bulk requests. New bulk requests implemented: Client::deleteDocuments(), Bulk::sendUdp()

2013-02-20
- Release candidate 0.20.5.0.RC1

2013-02-14
- Added factory for transports that is used by the Connection class
- The transport instances now has support for parameters that can be injected by specifying an array as a transport when creating the Elastica client

2013-02-08
- Terms->setScript() Added, namespace Elastica\Facet

2013-01-31
- Removed deprecated method Type::getType()
- Removed deprecated old constructor call in Filter\GeoDistance::__construct()
- Removed deprecated method Filter\Script::setQuery()
- Removed deprecated methods Query\QueryString::setTieBraker() and Query\QueryString::setQueryString()
- Removed deprecated methods Query\Builder::minimumShouldMatch() and Query\Builder::tieBreaker()

2013-01-25
- Add get/set/has/remove methods to Document
- Add magic methods __get/__set/__isset/__unset to Document
- Document::add method became deprecated, use set instead
- Populate document id created by elasticsearch on addDocument()/addDocuments() call if no document id was set
- Populate updated fields in document on Client::updateDocument() call if fields options is set

2013-01-24
- Added serialization support. Objects can be added to elastica directly when a serializer callable is configured on \Elastica\Type

2013-01-21
- Added Thrift transport. Ir requires installing munkie/elasticsearch-thrift-php package and elasticsearch-tranport-thrift plugin should be installed in elastcisearch

2013-01-13
- Add version option to Elastica_Search::search
- Remove compatibility for PHP 5.2
- Changed all syntax using namespaces, in compliance with PSR-2.
- Usage of composer for lib and test autoloading
- Added PHPUnit as a dev dependency in composer.json
- All tests were rewritten for new syntax.
- All tests where moved in Elastica\Test namespace
- All tests now inherit from Elastica\Test\Base
- Removed all executable flags on files where not needed.
- Update to elasticsearch 0.20.2
- Refactored Elastica_Script and added it support in Elastica_Query_CustomFiltersScore, Elastica_Query_CustomScore and Elastica_Filter_Script
- Refactored Elastica_Client::updateDocument() method to support partial document update. $data can be Elastic_Script, Elastic_Document or array.
- Elastica_Type::updateDocument() now takes Elastica_Document instead of Elastica_Script (BC break). Script can be set to document to perform script update.

2012-12-23
- Elastica_Client config param "servers" to "connections" renamed. "headers" and "curl" are now a config param inside "connections"
- Elastica_Connection added to allow connection management (enabled / disable)
- Refactoring of Elastica_Request. Takes Elastica_Connection in constructor instead of Elastica_Client
- Elastica_Transport refactored
- Elastica_Log refactored
- Renamed Elastica_Exception_Client to Elastica_Exception_Connection
- Use Elastica_Connection for the following constants: DEFAULT_PORT, DEFAULT_HOST, DEFAULT_TRANSPORT, TIMEOUT

2012-11-28
- Added Elastica_Filter_GeoDistanceRange filter

2012-11-23
- Simplified Elastica_Document data handling by extending Elastica_Param

2012-11-10
- Added Elastica_Cluster_Health, Elastica_Cluster_Health_Index and Elastica_Cluster_Health_Shard which wrap the _cluster/health endpoint.
- Added Elastica_Document::setId()
- Added options parameter to Elastica_Type::getDocument()
- Added Elastica_Query_Filtered::getFilter()

2012-10-30
- Elastica_Search implement Elastica_Searchable interface

2012-10-28
- Add Elastica_Filter_HasParent and Elastic_Query_HasParent

2012-08-11
- Release v0.19.8.0
- Elastica_Query_Prefix added

2012-07-26
- Change Elastica_Filter_GeoDistance::__construct(), accepts geohash parameter (BC break, before: ($key, $latitude, $longitude, $distance), after: ($key, $location, $distance) where $location is array('lat' => $latitude, 'lon' => $longitude) or a geohash)

2012-07-17
- Changed naming for several methods to camelCase
- Enforced PSR1 code style, as per https://github.com/pmjones/fig-standards/blob/psr-1-style-guide/proposed/PSR-1-basic.md
- Added Elastica_Script::toArray
- Added Elastica_ScriptFields
- Elastica_Query::setScriptFields now takes Elastica_ScriptFields or associative array as argument, the old implementation was bogus.

2012-06-24
- Simplify Elastica_Type::search and Elastica_Index::search by using Elastica_Search
- Implement Elastica_Filter_Abstract::setCache and Elastica_Filter_Abstract::setCacheKey
- Add Elastica_Param::hasParam
- Remove unsupported use of minimum number should match for Boolean Filter
- Remove old style path creation through params in Elastica_Index::create and Elastica_Search::search

2012-06-22
- Add Elastica_Filter_Limit
- Add getters+setters for Index Setting blocks 'read', 'write' and 'metadata'
- Add Elastica_Filter_MatchAll

2012-06-20
- Facet scope added

2012-06-09
- Change $_parent to null to also support 0 for an id
- Fix Elasitca_Document->toArray()

2012-05-01
- Release v0.19.3.0
- MoreLikeThis Query in Elastica_Document
- Add query param for request (allows GET params)

2012-03-04
- Node info call update. The receive os info and more, param is needed. By default, only basics are returned
- Release v0.19.0.0 which is compatible with ES 0.19.0 https://www.elastic.co/downloads/past-releases/0-19-0

2012-02-21
- Allow percolate queries in bulk requests
- Fix memory leak in curl requests

2012-01-23
- Packagist added http://packagist.org/

2012-01-15
- Vagrantfile for vagrant environment with elasticsearch added. Run: vagrant up

2012-01-08
- Allow to set curl params over client config [#106](https://github.com/ruflin/Elastica/issues/106/) [#107](https://github.com/ruflin/Elastica/issues/107/)
- Add the possibility to add path or url in config for a request [#120](https://github.com/ruflin/Elastica/issues/120/)

2012-01-04
- Elastica_Index::exists() and Elastica_Cluster::getIndexNames() added

2012-01-01
- Elastica_Cluster_Settings added
- Read only feature for cluster and index added. This feature is elasticsearch >0.19.0 only. ES 0.19.0 release is not out yet

2011-12-29
- Elastica_Type::deleteByQuery implemented

2011-12-20
- Release v0.18.6.0

2011-12-19
- Percolator for Type and Documents added

2011-12-06
- Elastica_Percolator added. See tests for more details

2011-12-02
- Rename Elastica_Type::getType() to Elastica_Type::getName(), getType() is now deprecated

2011-12-01
- Elastica_Filter_Term::addTerm renamed to setTerm, Elastica_Filter_Term::setTerm renamed to setRawTerm
- Elastica_Query_Term::addTerm renamed to setTerm, Elastica_Query_Term::setTerm renamed to setRawTerm

2011-11-30
- Release v0.18.5.0

2011-11-28
- Elastica_Filter_Nested added

2011-11-26
- Elastica_Search::addIndices(), Elastica_Search::addTypes() added

2011-11-20
- Release v0.18.4.1
- Elastica_Log added for logging. Has to be passed as client config to enable
- Elastica blogging introduced: http://ruflin.com/en/elastica

2011-11-17
- Release v0.18.4.0
- Support for Travis CI added: http://travis-ci.org/ruflin/Elastica

2011-11-07
- Elastica_Index_Stats added

2011-11-05
- Elastica_Query_Nested added

2011-10-29
- TTL for document and mapping added

2011-10-28
- Refactored Elastica_Query_CustomScore::addCSParam to ::addParams
- Rename Elastica_Query_CustomScore::addParam to ::addCSParam
- Release v0.18.1.0

2011-10-20
- Release v0.17.9.0
- Elastica_Filter_Type added

2011-10-19
- Elastica_Query_CustomFilterScore added

2011-10-15
- API Documentation changed to DocBlox

2011-10-10
- Bug fixing
- Release v0.17.8.0 added

2011-09-19
- Release v0.17.7.0 added
- Release v0.17.6.1 added

2011-09-18
- Elastica_Exception_ExpectedFieldNotFound renamed to Elastica_Exception_NotFound

2011-08-25
- Https transport layer added

2011-08-22
- Typo in Terms query fixed (issue [#74](https://github.com/ruflin/Elastica/issues/74/))

2011-08-15
- Refactoring HTTP connection to keep alive connection -> speed improvement during using the same client
- Release v0.17.6.0 added

2011-08-09
- Automatic creation of id for documents added. This was more a bug
- Release v0.17.4.0 added

2011-08-08
- Elastica_Query_Text added
- Params (constructor) of Elastica_Filter_GeoBoundingBox changed (array instead of single params)

2011-08-07
- Elastica_Query_MoreLikeThis added by @juneym. Still work under progress
- Refactoring Queries and Filters to use Elastica_Param. Adding tests

2011-08-05
- Elastica_Filter_Abstract enhanced for more general usage (set/get/addParam(s)) added

2011-08-04
- Release v0.17.3.0 added
- Elastica_Index_Settings::set/get response updated. get('...') does not require 'index.' in front anymore
- Nodes and Cluster shutdown added
- Elastica_Node::getIp() and getPort() added

2011-07-30
- Readd merge_factor to settings. Now working as expected. Index has to be closed first.

2011-07-29
- Release tag v0.17.2.0 added. Elastica is compatible with elasticsearch 0.17.2

2011-07-22
- Elastica_Index_Settings::getMergePolicyMergeFactor and set removed because of enhanced merge policy implementation in ES 0.17.0 https://github.com/elasticsearch/elasticsearch/issues/998
- Release tav v0.17.1.0 added

2011-07-21
- Elastica_Query_HasChild and _parent feature added by fabian
- Elastica_Filter_GeoBoundingBox added by fabian

2011-07-20
- Elastica_Query_Builder added by chrisdegrim

2011-07-19
- Release tag v0.17.0.0 added. Elastica is compatible with elasticsearch 0.17.0

2011-07-18
- ResultSet::hasFacets added
- QueryString useDisMax added

2011-07-15
- Facet/DateHistogram and Facet/Historgram added
- Documentation pages added unter http://ruflin.github.com/Elastica
- Release tag v0.16.4.0 added

2011-06-19
- Add support for multiple servers to Elastica_Client (issue [#39](https://github.com/ruflin/Elastica/issues/39/))

2011-06-16
- Support for multiple index, type queries and _all queries added through Elastica_Search object
- Elastica_Index::clearCache added to clean cache
- Elastica_Index::flush added

2011-06-07
- Elastica_Index::setNumberOfShards removed as not supported after creating index

2011-05-11
- Refactor client constructor. Elastica_Client::__construct(array $config) now takes a config array instead of host and port

2011-05-08
- Elastica_Query_QueryString::escapeTerm move to Elastica_Util::escapeTerm

2011-04-29
- Added getParam to Elastica_Result that more values can be retrieved from the hit array
- Elastica_Filter_Ids added https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-filter.html
- getMergePolicyMergeFactor and getRefreshInterval to Elastica_Type_Settings added. If no value is set, default values are returned

2011-04-28
- Release of version 0.16.0.0 (see new version naming structure in README)

2011-04-27
- Refactoring of Elastica_Type::setMapping. No source parameter anymore.
- Elastica_Type_Mapping object introduced to set more fine grained mapping

2011-04-17
- Elastica_Filter_Exists added

2011-04-14
- Elastica_Type getCount replace by count()
- Count has now optional query parametere

2011-04-01
- Renaming of functions in Elastica_Query_Terms and Ela-stica_Query_Filter to fit new naming convention. setTerms, addTerm have different API now!

2011-03-31
- Deprecated code removed
- Break backward compatibility to 0.15.1 (versions introduced by wlp1979)

2011-03-30
- Filtered query introduced
- setRawArguments in Elastica_Query is now setParam
- open / close for index added
- Remove Elastica_Filter and Elastica_Facets because not needed anymore

2011-03-29
- Renaming Elastica_Filter->addQuery, addFilter to setQuery, setFilter
- Add parts of Facets API
- Add facet Terms
- Renaming Elastica_Query->addFilter to setFilter

2011-03-24
- Renaming of Elastica_Status_Index to Elastica_Index_Status => API Change!
- IndexSettings added for improved bulk updating https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html

2011-03-21
- Node object added
- Node_Info and Node_Stats added
- Refactoring of Cluster object

2011-03-13
- changes.txt introduced
- getResponse in Elastica_Response renamed to getData. getResponse now deprecated
- Index status objects added
- getIndexName in Elastica_Index renamed to getName. getIndexName is deprecated

2011-03-21
- ChildrenAggregation added - https://www.elastic.co/guide/en/elasticsearch/guide/current/children-agg.html
