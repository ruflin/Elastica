<?php
namespace Elastica;

use Elastica\Query\AbstractQuery;

class Reindex extends Param
{
    const VERSION_TYPE = 'version_type';
    const VERSION_TYPE_INTERNAL = 'internal';
    const VERSION_TYPE_EXTERNAL = 'external';
    const OPERATION_TYPE = 'op_type';
    const OPERATION_TYPE_CREATE = 'create';
    const CONFLICTS = 'conflicts';
    const CONFLICTS_PROCEED = 'proceed';
    const TYPE = 'type';
    const SIZE = 'size';
    const QUERY = 'query';
    const REFRESH = 'refresh';
    const WAIT_FOR_COMPLETION = 'wait_for_completion';
    const WAIT_FOR_COMPLETION_FALSE = 'false';
    const WAIT_FOR_ACTIVE_SHARDS = 'wait_for_active_shards';
    const TIMEOUT = 'timeout';
    const SCROLL = 'scroll';
    const REQUESTS_PER_SECOND = 'requests_per_second';

    /**
     * @var Index
     */
    protected $_oldIndex;

    /**
     * @var Index
     */
    protected $_newIndex;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var Response|null
     */
    protected $_lastResponse;

    /**
     * @param array $options - deprecated because not compatible with complete Reindex API
     */
    public function __construct(Index $oldIndex, Index $newIndex, array $options = [])
    {
        $this->_oldIndex = $oldIndex;
        $this->_newIndex = $newIndex;
        $this->_params = $this->resolveOptions($options);
    }

    public function run()
    {
        $params = $this->_getEndpointParams($this->_params);
        $body = $this->_getBody($this->_oldIndex, $this->_newIndex, $this->_params);

        $reindexEndpoint = new \Elasticsearch\Endpoints\Reindex();
        $reindexEndpoint->setParams($params);
        $reindexEndpoint->setBody($body);

        $this->lastResponse = $this->_oldIndex->getClient()->requestEndpoint($reindexEndpoint);
        $this->_newIndex->refresh();

        return $this->_newIndex;
    }

    protected function _getBody($oldIndex, $newIndex, $params)
    {
        $body = array_diff_key($params, $this->_getEndpointParams($params));

        $body = array_merge_recursive($body, [
            'source' => ['index' => $oldIndex->getName()],
            'dest' => ['index' => $newIndex->getName()],
        ]);

        return $body;
    }

    protected function resolveOptions(array $options)
    {
        $params = array_merge([
            'source' => $this->_getSourcePartBody($options),
            'dest' => $this->_getDestPartBody($options),
        ], $this->_resolveBodyOptions($options));

        return $params;
    }

    protected function _getSourcePartBody($options)
    {
        $sourceBody = $this->_resolveSourceOptions($options);
        $sourceBody = $this->_setSourceQuery($sourceBody);
        $sourceBody = $this->_setSourceType($sourceBody);

        return $sourceBody;
    }

    protected function _getDestPartBody(array $options)
    {
        return $this->_resolveDestOptions($options);
    }

    private function _resolveSourceOptions(array $options)
    {
        return array_intersect_key($options, [
            self::TYPE => null,
            self::QUERY => null,
        ]);
    }

    private function _resolveDestOptions(array $options)
    {
        return array_intersect_key($options, [
            self::VERSION_TYPE => null,
            self::OPERATION_TYPE => null,
        ]);
    }

    private function _resolveBodyOptions(array $options)
    {
        return array_intersect_key($options, [
            self::SIZE => null,
            self::CONFLICTS => null,
        ]);
    }

    /**
     * @param array $sourceBody
     *
     * @return array
     */
    private function _setSourceQuery(array $sourceBody)
    {
        if (isset($sourceBody[self::QUERY]) && $sourceBody[self::QUERY] instanceof AbstractQuery) {
            $sourceBody[self::QUERY] = $sourceBody[self::QUERY]->toArray();
        }

        return $sourceBody;
    }

    /**
     * @param array $sourceBody
     *
     * @return array
     */
    private function _setSourceType(array $sourceBody)
    {
        if (isset($sourceBody[self::TYPE]) && !is_array($sourceBody[self::TYPE])) {
            $sourceBody[self::TYPE] = [$sourceBody[self::TYPE]];
        }
        if (isset($sourceBody[self::TYPE])) {
            foreach ($sourceBody[self::TYPE] as $key => $type) {
                if ($type instanceof Type) {
                    $sourceBody[self::TYPE][$key] = $type->getName();
                }
            }
        }

        return $sourceBody;
    }

    private function _getEndpointParams(array $params)
    {
        return array_intersect_key($params, [
            self::REFRESH => null,
            self::WAIT_FOR_COMPLETION => null,
            self::WAIT_FOR_ACTIVE_SHARDS => null,
            self::TIMEOUT => null,
            self::SCROLL => null,
            self::REQUESTS_PER_SECOND => null,
        ]);
    }

    public function setWaitForCompletion($value)
    {
        is_bool($value) && $value = $value ? 'true' : 'false';
        $this->setParam(self::WAIT_FOR_COMPLETION, $value);
    }

    public function setWaitForActiveShards($value)
    {
        $this->setParam(self::WAIT_FOR_ACTIVE_SHARDS, $value);
    }

    public function setTimeout($value)
    {
        $this->setParam(self::TIMEOUT, $value);
    }

    public function setScroll($value)
    {
        $this->setParam(self::SCROLL, $value);
    }

    public function setRequestsPerSecond($value)
    {
        $this->setParam(self::REQUESTS_PER_SECOND, $value);
    }

    public function setSourceParam(string $key, $value)
    {
        $this->_params['source'][$key] = $value;
    }

    public function setDestParam(string $key, $value)
    {
        $this->_params['dest'][$key] = $value;
    }

    public function getTaskId()
    {
        $taskId = null;
        if ($this->lastResponse instanceof Response) {
            $taskId = $this->lastResponse->getData()['task'] ?? null;
        }

        return $taskId;
    }
}
