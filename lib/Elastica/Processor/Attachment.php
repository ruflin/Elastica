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
    const DEFAULT_TARGET_FIELD_VALUE = 'attachment';
    const DEFAULT_INDEXED_CHARS_VALUE = 100000;
    const DEFAULT_IGNORE_MISSING_VALUE = false;

    /**
     * Attachment constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->setField($field);
    }

    /**
     * Set field.
     *
     * @param string $field
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
     * @param string $targetField
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
     * @param int $indexedChars
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
     * @param array $properties
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
     * @param bool $ignoreMissing
     *
     * @return $this
     */
    public function setIgnoreMissing(bool $ignoreMissing): self
    {
        return $this->setParam('ignore_missing', $ignoreMissing);
    }
}
