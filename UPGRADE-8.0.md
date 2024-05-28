UPGRADE FROM 7.3 to 8.0
=======================

Client configuration has been significantly reworked.
Elastica rely on official elasticsearch PHP client parameters now.

Client
-------
* `host` parameter has been renamed to `hosts` and it's type has been set to array
  *Before*
    ```php
    new Elastica\Elastica([
        'host' => 'http://es.host.com',
    ])
    ```

  *After*
    ```php
    new Elastica\Elastica([
        'hosts' => ['http://es.host.com'],
    ])
    ```

* `port` parameter has been removed.
  *Before*
    ```php
    new Elastica\Elastica([
        'host' => 'http://es.host.com',
        'port' => 9200,
    ])
    ```

  *After*
    ```php
    new Elastica\Elastica([
        'hosts' => ['http://es.host.com:9200'],
    ])
    ```

* `url` no longer supported in favor of `hosts`
    *Before*
    ```php
    new Elastica\Elastica([
        'url' => 'http://es.host.com',
    ])
    ```

    *After*
    ```php
    new Elastica\Elastica([
        'hosts' => ['http://es.host.com'],
    ])
    ```

* `roundRobin` parameter has been removed
  *Before*
    ```php
    new Elastica\Elastica([
        'url' => 'http://es.host.com',
        'roundRobin' => true,
    ])
    ```

  *After*
    ```php
    use Elastic\Transport\NodePool\Resurrect\ElasticsearchResurrect;
    use Elastic\Transport\NodePool\Selector\RoundRobin;
    use Elastic\Transport\NodePool\SimpleNodePool;

    $nodePool = new SimpleNodePool(
        new RoundRobin(),
        new ElasticsearchResurrect()
    );

    new Client([
        'hosts' => [
            'https://es.host.com:9200',
        ],
        'transport_config' => [
            'node_pool' => $nodePool,
        ],
    ]);
    ```

* `connections` parameter has been removed.
  *Before*
    ```php
    new Elastica\Elastica([
        'connections' => [
            [
                'host' => 'https://es.node1.com',
                'port' => 9200,
            ],
            [
                'host' => 'https://es.node2.com',
                'port' => 9200,
            ],
        ],
    ])
    ```

  *After*
    ```php
    new Elastica\Elastica([
        'hosts' => [
            'http://es.host.com',
            'https://es.node2.com',
        ],
    ])
    ```
