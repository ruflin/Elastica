<?php

namespace Elastica;

/**
 * Base class for things that can be sent to the update api (Document and
 * Script).
 *
 * @author   Nik Everett <nik9000@gmail.com>
 */
class AbstractUpdateAction extends Param
{
    /**
     * @var Document
     */
    protected $_upsert;

    /**
     * Sets the id of the document.
     */
    public function setId(?string $id = null): self
    {
        return $this->setParam('_id', $id);
    }

    /**
     * Returns document id.
     *
     * @return string|null Document id
     */
    public function getId(): ?string
    {
        return $this->hasParam('_id') ? $this->getParam('_id') : null;
    }

    public function hasId(): bool
    {
        return null !== $this->getId();
    }

    /**
     * Sets the document index name.
     *
     * @param Index|string $index Index name
     */
    public function setIndex($index): self
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        return $this->setParam('_index', $index);
    }

    /**
     * Get the document index name.
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return string Index name
     */
    public function getIndex()
    {
        return $this->getParam('_index');
    }

    /**
     * Sets the version parameters of a document for use with optimistic concurrency control.
     *
     * @return $this
     */
    public function setVersionParams(array $responseData): self
    {
        if (isset($responseData['_version'])) {
            $this->setVersion($responseData['_version']);
        }

        if (isset($responseData['_seq_no'])) {
            $this->setSequenceNumber($responseData['_seq_no']);
        }

        if (isset($responseData['_primary_term'])) {
            $this->setPrimaryTerm($responseData['_primary_term']);
        }

        return $this;
    }

    /**
     * Sets the sequence number of a document for use with optimistic concurrency control.
     *
     * @param int $number Sequence Number
     *
     * @return $this
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/6.8/optimistic-concurrency-control.html
     */
    public function setSequenceNumber(int $number): self
    {
        return $this->setParam('if_seq_no', $number);
    }

    /**
     * Returns document version.
     *
     * @return int Document version
     */
    public function getSequenceNumber(): int
    {
        return $this->getParam('if_seq_no');
    }

    public function hasSequenceNumber(): bool
    {
        return $this->hasParam('if_seq_no');
    }

    /**
     * Sets the primary term of a document for use with optimistic concurrency control.
     *
     * @param int $term Primary Term
     *
     * @return $this
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/6.8/optimistic-concurrency-control.html
     */
    public function setPrimaryTerm(int $term): self
    {
        return $this->setParam('if_primary_term', $term);
    }

    /**
     * Returns document version.
     *
     * @return int Document version
     */
    public function getPrimaryTerm(): int
    {
        return $this->getParam('if_primary_term');
    }

    public function hasPrimaryTerm(): bool
    {
        return $this->hasParam('if_primary_term');
    }

    /**
     * Sets the version of a document.
     *
     * @param int $version Document version
     *
     * @return $this
     *
     * @see https://www.elastic.co/blog/versioning
     */
    public function setVersion($version)
    {
        return $this->setParam('version', (int) $version);
    }

    /**
     * Returns document version.
     *
     * @return int|string Document version
     */
    public function getVersion()
    {
        return $this->getParam('version');
    }

    /**
     * @return bool
     */
    public function hasVersion()
    {
        return $this->hasParam('version');
    }

    /**
     * Set operation type.
     *
     * @param string $opType Only accept create
     *
     * @return $this
     */
    public function setOpType($opType)
    {
        return $this->setParam('op_type', $opType);
    }

    /**
     * Get operation type.
     *
     * @return string
     */
    public function getOpType()
    {
        return $this->getParam('op_type');
    }

    /**
     * @return bool
     */
    public function hasOpType()
    {
        return $this->hasParam('op_type');
    }

    /**
     * Set routing query param.
     *
     * @param string $value routing
     *
     * @return $this
     */
    public function setRouting($value)
    {
        return $this->setParam('routing', $value);
    }

    /**
     * Get routing parameter.
     *
     * @return string
     */
    public function getRouting()
    {
        return $this->getParam('routing');
    }

    /**
     * @return bool
     */
    public function hasRouting()
    {
        return $this->hasParam('routing');
    }

    /**
     * @param array|string $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        if (\is_array($fields)) {
            $fields = \implode(',', $fields);
        }

        return $this->setParam('fields', (string) $fields);
    }

    /**
     * @return $this
     */
    public function setFieldsSource()
    {
        return $this->setFields('_source');
    }

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->getParam('fields');
    }

    /**
     * @return bool
     */
    public function hasFields()
    {
        return $this->hasParam('fields');
    }

    /**
     * @param int $num
     *
     * @return $this
     */
    public function setRetryOnConflict($num)
    {
        return $this->setParam('retry_on_conflict', (int) $num);
    }

    /**
     * @return int
     */
    public function getRetryOnConflict()
    {
        return $this->getParam('retry_on_conflict');
    }

    /**
     * @return bool
     */
    public function hasRetryOnConflict()
    {
        return $this->hasParam('retry_on_conflict');
    }

    /**
     * @param bool|string $refresh
     *
     * @return $this
     */
    public function setRefresh($refresh = true)
    {
        \is_bool($refresh) && $refresh = $refresh
            ? Reindex::REFRESH_TRUE
            : Reindex::REFRESH_FALSE;

        return $this->setParam(Reindex::REFRESH, $refresh);
    }

    /**
     * @return bool|string
     */
    public function getRefresh()
    {
        $refresh = $this->getParam('refresh');

        return \in_array($refresh, [Reindex::REFRESH_TRUE, Reindex::REFRESH_FALSE], true)
            ? Reindex::REFRESH_TRUE === $refresh
            : $refresh;
    }

    /**
     * @return bool
     */
    public function hasRefresh()
    {
        return $this->hasParam('refresh');
    }

    /**
     * @param string $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        return $this->setParam('timeout', $timeout);
    }

    /**
     * @return bool
     */
    public function getTimeout()
    {
        return $this->getParam('timeout');
    }

    /**
     * @return bool
     */
    public function hasTimeout()
    {
        return $this->hasParam('timeout');
    }

    /**
     * @param string $timeout
     *
     * @return $this
     */
    public function setConsistency($timeout)
    {
        return $this->setParam('consistency', $timeout);
    }

    /**
     * @return string
     */
    public function getConsistency()
    {
        return $this->getParam('consistency');
    }

    /**
     * @return bool
     */
    public function hasConsistency()
    {
        return $this->hasParam('consistency');
    }

    /**
     * @param string $timeout
     *
     * @return $this
     */
    public function setReplication($timeout)
    {
        return $this->setParam('replication', $timeout);
    }

    /**
     * @return string
     */
    public function getReplication()
    {
        return $this->getParam('replication');
    }

    /**
     * @return bool
     */
    public function hasReplication()
    {
        return $this->hasParam('replication');
    }

    /**
     * @param array|Document $data
     *
     * @return $this
     */
    public function setUpsert($data)
    {
        $document = Document::create($data);
        $this->_upsert = $document;

        return $this;
    }

    /**
     * @return Document
     */
    public function getUpsert()
    {
        return $this->_upsert;
    }

    /**
     * @return bool
     */
    public function hasUpsert()
    {
        return null !== $this->_upsert;
    }

    /**
     * @param array $fields if empty array all options will be returned
     *
     * @return array
     */
    public function getOptions(array $fields = [])
    {
        if (!empty($fields)) {
            return \array_filter(\array_intersect_key($this->getParams(), \array_flip($fields)));
        }

        return \array_filter($this->getParams());
    }
}
