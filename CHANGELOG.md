# Change Log
All notable changes to this project will be documented in this file based on the [Keep a Changelog](http://keepachangelog.com/) Standard. This project adheres to [Semantic Versioning](http://semver.org/).


## [Unreleased](https://github.com/ruflin/Elastica/compare/2.3.1...HEAD)

### Backward Compatibility Breaks

### Bugfixes

### Added

### Improvements

### Deprecated


## [2.3.1](https://github.com/ruflin/Elastica/releases/tag/2.3.1) - 2015-10-17

### Bugfixes
- Filters aggregation: empty name is named bucket #935
- Prevent mix keys in filters (#936) #939
- Fix empty string is not anonymous filter #935
- Filters aggregation: empty name is named bucket #935

### Added
- Support for field_value_factor #953
- Added setMinDocCount and setExtendedBounds options #947
- Avoid environment dependecies in tests #938

### Improvements
- Update elasticsearch dependency to elasticsearch 1.7.3 #957

### Deprecated
- Added exceptions of deprecated transports to deprecation list


## [2.3.0](https://github.com/ruflin/Elastica/releases/tag/2.3.0) - 2015-09-15


### Backward Compatibility Breaks
- Objects do not casts to arrays in setters and saved in params as objects. There is many side effects if
  you work with params on "low-level" or change your objects after you call setter with object
  as argument. [#916](https://github.com/ruflin/Elastica/pull/916)

### Added
- Add Script File feature #902 #914

### Improvements
- Support the http.compression in the Http transport adapter #515
- Introduction of Lazy toArray [#916](https://github.com/ruflin/Elastica/pull/916)
- Update Elasticsearch dependency to 1.7.2 [#929](https://github.com/ruflin/Elastica/pull/929)



## [2.2.1](https://github.com/ruflin/Elastica/releases/tag/2.2.1) - 2015-08-10


### Added
- Support for index template added [#905](https://github.com/ruflin/Elastica/pull/905)

### Improvements
- Update Elasticsearch dependency to 1.7.1 and update plugin dependencies [#909](https://github.com/ruflin/Elastica/pull/909)
- Update php-cs-fixer to 1.10 [#898](https://github.com/ruflin/Elastica/pull/898)
- Elastica\QueryBuilder now uses Elastica\QueryBuilder\Version\Latest as default version to avoid empty version classes. [#897](https://github.com/ruflin/Elastica/pull/897)
- Update elasticseach-image to work with ES 1.7.1 [#907](https://github.com/ruflin/Elastica/pull/907)
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
- Support for retrieving id node #852
- Scroll Iterator [#842](https://github.com/ruflin/Elastica/issues/842/)
- Gitter Elastica Chat Room add for Elastica discussions: https://gitter.im/ruflin/Elastica
- Introduce PHP7 compatibility and tests. #837
- `Tool\CrossIndex` for reindexing and copying data and mapping between indices [#853](https://github.com/ruflin/Elastica/pull/853)
- CONTIRUBTING.md file added for contributor guidelines. #854

### Improvements
- Introduction of Changelog standard based on http://keepachangelog.com/. changes.txt moved to CHANGELOG.md [#844](https://github.com/ruflin/Elastica/issues/844/)
- Make host for all tests dynamic to prepare it for a more dynamic test environment #846
- Node information is retrieved based on id instead of name as multiple nodes can have the same name. #852
- Guzzle Http dependency updated to 5.3.*
- Remove NO_DEV builds from travis build matrix to speed up building. All builds include no dev packages.
- Introduction of benchmark test group to make it easy to run benchmark tests.
- Make the docker images directly [available](https://hub.docker.com/u/ruflin/) on the docker registry. This speeds up fetching of the images and automates the build of the images.

### Backward Compatibility Breaks
- `Elastica\ScanAndScroll::$_lastScrollId` removed: `key()` now always returns the next scroll id [#842](https://github.com/ruflin/Elastica/issues/842/)


### Deprecated
- Facets are deprecated. You are encouraged to migrate to aggregations instead. [#855](https://github.com/ruflin/Elastica/pull/855/)
- Elastica\Query\Builder is deprecated. Use new Elastica\QueryBuilder instead. [#855](https://github.com/ruflin/Elastica/pull/855/)
- For PHP 7 compatibility Elastica\Query\Bool was renamed to *\BoolQuery, Elastica\Filter\Bool was renamed to BoolFilter, Elastica\Transport\Null was renamed to NullTransport as Null and Bool are reserved phrases in PHP 7. Proxy objects for all three exist to keep backward compatibility. It is recommended to start using the new objects as the proxy classes will be deprecated as soon as PHP 7 is stable. #837



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
- Allow bool in Query::setSource function [#818](https://github.com/ruflin/Elastica/issues/818/) http://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-source-filtering.html
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
- Add setPostFilter method to Elastica\Query (http://www.elastic.co/guide/en/elasticsearch/guide/current/_post_filter.html) [#645](https://github.com/ruflin/Elastica/issues/645/)

2014-06-30
- Add Reverse Nested aggregation (http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html). [#642](https://github.com/ruflin/Elastica/issues/642/)

2014-06-14
- Release v1.2.1.0
- Removed the requirement to set arguments filter and/or query in Filtered, according to the documentation: http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-filtered-query.html [#616](https://github.com/ruflin/Elastica/issues/616/)

2014-06-13
- Stop ClientTest->testDeleteIdsIdxStringTypeString from failing 1/3 of the time [#634](https://github.com/ruflin/Elastica/issues/634/)
- Stop ScanAndScrollTest->testQuerySizeOverride from failing frequently for no reason [#635](https://github.com/ruflin/Elastica/issues/635/)
- rework Document and Script so they can share some infrastructure allowing scripts to specify things like _retry_on_conflict and _routing [#629](https://github.com/ruflin/Elastica/issues/629/)

2014-06-11
- Allow bulk API deletes to be routed [#631](https://github.com/ruflin/Elastica/issues/631/)

2014-06-10
- Update travis to elasticsearch 1.2.1, disable Thrift plugin as not compatible and fix incompatible tests

2014-06-04
- Implement Boosting Query (http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html) [#625](https://github.com/ruflin/Elastica/issues/625/)

2014-06-02
- add retry_on_conflict support to bulk [#623](https://github.com/ruflin/Elastica/issues/623/)

2014-06-01
- toString updated to consider doc_as_upsert if sent an array source [#622](https://github.com/ruflin/Elastica/issues/622/)

2014-05-27
- Fix Aggragations/Filter to work with es v1.2.0 [#619](https://github.com/ruflin/Elastica/issues/619/)

2014-05-25
- Added Guzzle transport as an alternative to the default Http transport [#618](https://github.com/ruflin/Elastica/issues/618/)
- Added Elastica\ScanAndScroll Iterator (http://www.elastic.co/guide/en/elasticsearch/guide/current/scan-scroll.html) [#617](https://github.com/ruflin/Elastica/issues/617/)

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
  (see http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html)

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
  See http://www.elastic.co/guide/en/elasticsearch/reference/current/indices-types-exists.html#indices-types-exists

2013-10-27
- Adapted possible values (not only in) for minimum_should_match param based on elasticsearch documetnation http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html

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
  See http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-term.html

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
- Elastica_Filter_Ids added http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-filter.html
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
- IndexSettings added for improved bulk updating http://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html

2011-03-21
- Node object added
- Node_Info and Node_Stats added
- Refactoring of Cluster object

2011-03-13
- changes.txt introduced
- getResponse in Elastica_Response renamed to getData. getResponse now deprecated
- Index status objects added
- getIndexName in Elastica_Index renamed to getName. getIndexName is deprecated
