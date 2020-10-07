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
    public function getIndex(): string
    {
        return $this->getParam('_index');
    }

    /**
     * Sets the version parameters of a document for use with optimistic concurrency control.
     *
     * @param array $responseData
     *
     * @return $this
     */
    public function setVersionParams(array $responseData): self
    {
        if (isset($responseData['_version'])) {
            $this->setVersion($responseData['_version']);
        }

        if (isset($data['_seq_no'])) {
            $this->setSequenceNumber($responseData['_seq_no']);
        }

        if (isset($data['_primary_term'])) {
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

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
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
    public function setVersion(int $version): self
    {
        return $this->setParam('_version', $version);
    }

    /**
     * Returns document version.
     *
     * @return int Document version
     */
    public function getVersion(): int
    {
        return $this->getParam('_version');
    }

    /**
     * @return bool
     */
    public function hasVersion(): bool
    {
        return $this->hasParam('_version');
    }

    /**
     * Set operation type.
     *
     * @param string $opType Only accept create
     *
     * @return $this
     */
    public function setOpType(string $opType): self
    {
        return $this->setParam('op_type', $opType);
    }

    /**
     * Get operation type.
     *
     * @return string
     */
    public function getOpType(): string
    {
        return $this->getParam('op_type');
    }

    /**
     * @return bool
     */
    public function hasOpType(): bool
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
    public function setRouting(string $value): self
    {
        return $this->setParam('routing', $value);
    }

    /**
     * Get routing parameter.
     *
     * @return string
     */
    public function getRouting(): string
    {
        return $this->getParam('_routing');
    }

    /**
     * @return bool
     */
    public function hasRouting(): bool
    {
        return $this->hasParam('_routing');
    }

    /**
     * @param array|string $fields
     *
     * @return $this
     */
    public function setFields($fields): self
    {
        if (\is_array($fields)) {
            $fields = \implode(',', $fields);
        }

        return $this->setParam('fields', (string) $fields);
    }

    /**
     * @return $this
     */
    public function setFieldsSource(): self
    {
        return $this->setFields('_source');
    }

    /**
     * @return string
     */
    public function getFields(): string
    {
        return $this->getParam('fields');
    }

    /**
     * @return bool
     */
    public function hasFields(): bool
    {
        return $this->hasParam('fields');
    }

    /**
     * @param int $num
     *
     * @return $this
     */
    public function setRetryOnConflict(int $num): self
    {
        return $this->setParam('retry_on_conflict', (int) $num);
    }

    /**
     * @return int
     */
    public function getRetryOnConflict(): int
    {
        return $this->getParam('retry_on_conflict');
    }

    /**
     * @return bool
     */
    public function hasRetryOnConflict(): bool
    {
        return $this->hasParam('_retry_on_conflict');
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

        return \in_array($refresh, [Reindex::REFRESH_TRUE, Reindex::REFRESH_FALSE])
            ? Reindex::REFRESH_TRUE === $refresh
            : $refresh;
    }

    /**
     * @return bool
     */
    public function hasRefresh(): bool
    {
        return $this->hasParam('refresh');
    }

    /**
     * @param string $timeout
     *
     * @return $this
     */
    public function setTimeout(string $timeout): self
    {
        return $this->setParam('timeout', $timeout);
    }

    /**
     * @return string
     */
    public function getTimeout(): string
    {
        return $this->getParam('timeout');
    }

    /**
     * @return bool
     */
    public function hasTimeout(): bool
    {
        return $this->hasParam('timeout');
    }

    /**
     * @param string $timeout
     *
     * @return $this
     */
    public function setConsistency(string $timeout): self
    {
        return $this->setParam('consistency', $timeout);
    }

    /**
     * @return string
     */
    public function getConsistency(): string
    {
        return $this->getParam('consistency');
    }

    /**
     * @return bool
     */
    public function hasConsistency(): bool
    {
        return $this->hasParam('consistency');
    }

    /**
     * @param string $timeout
     *
     * @return $this
     */
    public function setReplication(string $timeout): self
    {
        return $this->setParam('replication', $timeout);
    }

    /**
     * @return string
     */
    public function getReplication(): string
    {
        return $this->getParam('replication');
    }

    /**
     * @return bool
     */
    public function hasReplication(): bool
    {
        return $this->hasParam('replication');
    }

    /**
     * @param array|Document $data
     *
     * @return $this
     */
    public function setUpsert($data): self
    {
        $document = Document::create($data);
        $this->_upsert = $document;

        return $this;
    }

    /**
     * @return Document
     */
    public function getUpsert(): Document
    {
        return $this->_upsert;
    }

    /**
     * @return bool
     */
    public function hasUpsert(): bool
    {
        return null !== $this->_upsert;
    }

    /**
     * @param array $fields if empty array all options will be returned
     *
     * @return array
     */
    public function getOptions(array $fields = []): array
    {
        if (!empty($fields)) {
            return \array_filter(\array_intersect_key($this->getParams(), \array_flip($fields)));
        }

        return \array_filter($this->getParams());
    }
}
