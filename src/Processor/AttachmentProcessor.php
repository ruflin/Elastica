<?php

namespace Elastica\Processor;

/**
 * Elastica Attachment Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/plugins/current/ingest-attachment.html
 */
class AttachmentProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreMissingTrait;
    use Traits\TargetFieldTrait;

    public const DEFAULT_TARGET_FIELD_VALUE = 'attachment';
    public const DEFAULT_INDEXED_CHARS_VALUE = 100000;
    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    public function __construct(string $field)
    {
        $this->setField($field);
    }

    /**
     * Set indexed_chars. Default 100000.
     *
     * @return $this
     */
    public function setIndexedChars(int $indexedChars): self
    {
        return $this->setParam('indexed_chars', $indexedChars);
    }

    /**
     * Set properties. Default all properties. Can be content, title, name, author, keywords, date, content_type, content_length, language.
     *
     * @return $this
     */
    public function setProperties(array $properties): self
    {
        return $this->setParam('properties', $properties);
    }
}
