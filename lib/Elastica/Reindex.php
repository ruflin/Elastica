<?php
namespace Elastica;

use Elastica\Query\AbstractQuery;

class Reindex
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

    public function __construct(Index $oldIndex, Index $newIndex, array $options = [])
    {
        $this->_oldIndex = $oldIndex;
        $this->_newIndex = $newIndex;
        $this->_options = $options;
    }

    public function run()
    {
        $body = $this->_getBody($this->_oldIndex, $this->_newIndex, $this->_options);

        $reindexEndpoint = new \Elasticsearch\Endpoints\Reindex();
        $reindexEndpoint->setBody($body);

        $this->_oldIndex->getClient()->requestEndpoint($reindexEndpoint);
        $this->_newIndex->refresh();

        return $this->_newIndex;
    }

    protected function _getBody($oldIndex, $newIndex, $options)
    {
        $body = array_merge([
            'source' => $this->_getSourcePartBody($oldIndex, $options),
            'dest' => $this->_getDestPartBody($newIndex, $options),
        ], $this->_resolveBodyOptions($options));

        return $body;
    }

    protected function _getSourcePartBody(Index $index, array $options)
    {
        $sourceBody = array_merge([
            'index' => $index->getName(),
        ], $this->_resolveSourceOptions($options));

        $sourceBody = $this->_setSourceQuery($sourceBody);
        $sourceBody = $this->_setSourceType($sourceBody);

        return $sourceBody;
    }

    protected function _getDestPartBody(Index $index, array $options)
    {
        return array_merge([
            'index' => $index->getName(),
        ], $this->_resolveDestOptions($options));
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
}
