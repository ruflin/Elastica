<?php

namespace Elastica;

use Elastica\Query\AbstractQuery;
use Elastica\Script\AbstractScript;
use Elastica\Script\Script;

class Reindex extends Param
{
    public const VERSION_TYPE = 'version_type';
    public const VERSION_TYPE_INTERNAL = 'internal';
    public const VERSION_TYPE_EXTERNAL = 'external';
    public const OPERATION_TYPE = 'op_type';
    public const OPERATION_TYPE_CREATE = 'create';
    public const CONFLICTS = 'conflicts';
    public const CONFLICTS_PROCEED = 'proceed';
    public const SIZE = 'size';
    public const QUERY = 'query';
    public const SORT = 'sort';
    public const SCRIPT = 'script';
    public const SOURCE = '_source';
    public const REMOTE = 'remote';
    public const SLICE = 'slice';
    public const REFRESH = 'refresh';
    public const REFRESH_TRUE = 'true';
    public const REFRESH_FALSE = 'false';
    public const REFRESH_WAIT_FOR = 'wait_for';
    public const WAIT_FOR_COMPLETION = 'wait_for_completion';
    public const WAIT_FOR_COMPLETION_FALSE = 'false';
    public const WAIT_FOR_ACTIVE_SHARDS = 'wait_for_active_shards';
    public const TIMEOUT = 'timeout';
    public const SCROLL = 'scroll';
    public const REQUESTS_PER_SECOND = 'requests_per_second';
    public const PIPELINE = 'pipeline';
    public const SLICES = 'slices';
    public const SLICES_AUTO = 'auto';

    /**
     * @var Index
     */
    protected $_oldIndex;

    /**
     * @var Index
     */
    protected $_newIndex;

    /**
     * @var Response|null
     */
    protected $_lastResponse;

    public function __construct(Index $oldIndex, Index $newIndex, array $params = [])
    {
        $this->_oldIndex = $oldIndex;
        $this->_newIndex = $newIndex;

        $this->setParams($params);
    }

    public function run(): Response
    {
        $body = $this->_getBody($this->_oldIndex, $this->_newIndex, $this->getParams());

        $reindexEndpoint = new \Elasticsearch\Endpoints\Reindex();
        $params = \array_intersect_key($this->getParams(), \array_fill_keys($reindexEndpoint->getParamWhitelist(), null));
        $reindexEndpoint->setParams($params);
        $reindexEndpoint->setBody($body);

        $this->_lastResponse = $this->_oldIndex->getClient()->requestEndpoint($reindexEndpoint);

        return $this->_lastResponse;
    }

    public function setWaitForCompletion($value): void
    {
        \is_bool($value) && $value = $value ? 'true' : 'false';

        $this->setParam(self::WAIT_FOR_COMPLETION, $value);
    }

    public function setWaitForActiveShards($value): void
    {
        $this->setParam(self::WAIT_FOR_ACTIVE_SHARDS, $value);
    }

    public function setTimeout($value): void
    {
        $this->setParam(self::TIMEOUT, $value);
    }

    public function setScroll($value): void
    {
        $this->setParam(self::SCROLL, $value);
    }

    public function setRequestsPerSecond($value): void
    {
        $this->setParam(self::REQUESTS_PER_SECOND, $value);
    }

    public function setScript(Script $script): void
    {
        $this->setParam(self::SCRIPT, $script);
    }

    public function setQuery(AbstractQuery $query): void
    {
        $this->setParam(self::QUERY, $query);
    }

    public function setPipeline(Pipeline $pipeline): void
    {
        $this->setParam(self::PIPELINE, $pipeline);
    }

    public function setRefresh(string $value): void
    {
        $this->setParam(self::REFRESH, $value);
    }

    public function getTaskId()
    {
        $taskId = null;
        if ($this->_lastResponse instanceof Response) {
            $taskId = $this->_lastResponse->getData()['task'] ? $this->_lastResponse->getData()['task'] : null;
        }

        return $taskId;
    }

    protected function _getBody(Index $oldIndex, Index $newIndex, array $params): array
    {
        $body = \array_merge([
            'source' => $this->_getSourcePartBody($oldIndex, $params),
            'dest' => $this->_getDestPartBody($newIndex, $params),
        ], $this->_resolveBodyOptions($params));

        return $this->_setBodyScript($body);
    }

    protected function _getSourcePartBody(Index $index, array $params): array
    {
        $sourceBody = \array_merge([
            'index' => $index->getName(),
        ], $this->_resolveSourceOptions($params));

        return $this->_setSourceQuery($sourceBody);
    }

    protected function _getDestPartBody(Index $index, array $params): array
    {
        $destBody = \array_merge([
            'index' => $index->getName(),
        ], $this->_resolveDestOptions($params));

        // Resolves the pipeline name
        $pipeline = $destBody[self::PIPELINE] ?? null;
        if ($pipeline instanceof Pipeline) {
            $destBody[self::PIPELINE] = $pipeline->getId();
        }

        return $destBody;
    }

    private function _resolveSourceOptions(array $params): array
    {
        return \array_intersect_key($params, [
            self::QUERY => null,
            self::SORT => null,
            self::SOURCE => null,
            self::REMOTE => null,
            self::SLICE => null,
        ]);
    }

    private function _resolveDestOptions(array $params): array
    {
        return \array_intersect_key($params, [
            self::VERSION_TYPE => null,
            self::OPERATION_TYPE => null,
            self::PIPELINE => null,
        ]);
    }

    private function _resolveBodyOptions(array $params): array
    {
        return \array_intersect_key($params, [
            self::SIZE => null,
            self::CONFLICTS => null,
        ]);
    }

    private function _setSourceQuery(array $sourceBody): array
    {
        if (isset($sourceBody[self::QUERY]) && $sourceBody[self::QUERY] instanceof AbstractQuery) {
            $sourceBody[self::QUERY] = $sourceBody[self::QUERY]->toArray();
        }

        return $sourceBody;
    }

    private function _setBodyScript(array $body): array
    {
        if (!$this->hasParam(self::SCRIPT)) {
            return $body;
        }

        $script = $this->getParam(self::SCRIPT);

        if ($script instanceof AbstractScript) {
            $body = \array_merge($body, $script->toArray());
        } else {
            $body[self::SCRIPT] = $script;
        }

        return $body;
    }
}
