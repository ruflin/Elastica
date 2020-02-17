<?php

namespace Elastica\Processor;

/**
 * Elastica Attachment Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/plugins/current/ingest-attachment.html
 */
class Attachment extends AbstractProcessor
{
    public const DEFAULT_TARGET_FIELD_VALUE = 'attachment';
    public const DEFAULT_INDEXED_CHARS_VALUE = 100000;
    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    /**
     * Attachment constructor.
     */
    public function __construct(string $field)
    {
        $this->setField($field);
    }

    /**
     * Set field.
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set target_field. Default attachment.
     *
     * @return $this
     */
    public function setTargetField(string $targetField): self
    {
        return $this->setParam('target_field', $targetField);
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

    /**
     * Set ignore_missing. Default value false.
     *
     * @return $this
     */
    public function setIgnoreMissing(bool $ignoreMissing): self
    {
        return $this->setParam('ignore_missing', $ignoreMissing);
    }
}
