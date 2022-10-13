<?php

namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;
use Elastica\ApiVersion;
use Elastica\Bulk\Action;
use Elastica\Document;
use Elastica\Script\AbstractScript;
use Elastica\Type;

abstract class AbstractDocument extends Action
{
    /**
     * @var AbstractScript|Document
     */
    protected $_data;

    /**
     * @param AbstractScript|Document $document
     */
    public function __construct($document, int $apiVersion)
    {
        $this->apiVersion = $apiVersion;
        $this->setData($document);
    }

    /**
     * @return $this
     */
    public function setDocument(Document $document): self
    {
        $this->_data = $document;

        $metadata = $this->_getMetadata($document);

        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @return $this
     */
    public function setScript(AbstractScript $script): self
    {
        if (!$this instanceof UpdateDocument) {
            throw new \BadMethodCallException('setScript() can only be used for UpdateDocument');
        }

        $this->_data = $script;

        $metadata = $this->_getMetadata($script);
        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @param AbstractScript|Document $data
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setData($data): self
    {
        if ($data instanceof AbstractScript) {
            $this->setScript($data);
        } elseif ($data instanceof Document) {
            $this->setDocument($data);
        } else {
            throw new \InvalidArgumentException('Data should be a Document or a Script.');
        }

        return $this;
    }

    /**
     * Note: This is for backwards compatibility.
     *
     * @return Document|null
     */
    public function getDocument()
    {
        if (!$this->_data instanceof Document) {
            return null;
        }

        return $this->_data;
    }

    /**
     * Note: This is for backwards compatibility.
     *
     * @return AbstractScript|null
     */
    public function getScript()
    {
        if (!$this->_data instanceof AbstractScript) {
            return null;
        }

        return $this->_data;
    }

    /**
     * @return AbstractScript|Document
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Creates a bulk action for a document or a script.
     *
     * The action can be index, update, create or delete based on the $opType param (by default index).
     *
     * @param AbstractScript|Document $data
     *
     * @return AbstractDocument
     */
    public static function create($data, ?string $opType = null, int $apiVersion): self
    {
        // Check type
        if (!$data instanceof Document && !$data instanceof AbstractScript) {
            throw new \InvalidArgumentException('The data needs to be a Document or a Script.');
        }

        if (null === $opType && $data->hasOpType()) {
            $opType = $data->getOpType();
        }

        // Check that scripts can only be used for updates
        if ($data instanceof AbstractScript) {
            if (null === $opType) {
                $opType = self::OP_TYPE_UPDATE;
            } elseif (self::OP_TYPE_UPDATE !== $opType) {
                throw new \InvalidArgumentException('Scripts can only be used with the update operation type.');
            }
        }

        switch ($opType) {
            case self::OP_TYPE_DELETE:
                return new DeleteDocument($data, $apiVersion);

            case self::OP_TYPE_CREATE:
                return new CreateDocument($data, $apiVersion);

            case self::OP_TYPE_UPDATE:
                return new UpdateDocument($data, $apiVersion);

            case self::OP_TYPE_INDEX:
            default:
                return new IndexDocument($data, $apiVersion);
        }
    }

    abstract protected function _getMetadata(AbstractUpdateAction $source): array;

    protected function handleMetadataByApiVersion(array $metadata): array {
        if ($this->apiVersion === ApiVersion::API_VERSION_6) {
            // @see https://github.com/BrandEmbassy/platform-backend/blob/206169d2c8b69a48ce7b59dab1cf6f5159621df0/application/src/BE/ElasticSearch/Index/Index.php#L73-L80
            $metadata['_type'] = in_array(
                    $metadata['_index'],
                    [
                        'visitors',
                        'visitor_events_full_history_read_only'
                    ]
                )
                ? Type::DOC
                : Type::DEFAULT;
        }

        return $metadata;
    }
}
