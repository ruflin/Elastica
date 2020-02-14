<?php

namespace Elastica\Query;

use Elastica\Document;

/**
 * Percolate query.
 *
 * @author Boris Popovschi <zyqsempai@mail.ru>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-percolate-query.html
 */
/**
 * Class Percolate.
 */
class Percolate extends AbstractQuery
{
    /**
     * The field of type percolator and that holds the indexed queries. This is a required parameter.
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * The source of the document being percolated.
     *
     * @param array|Document $document
     *
     * @return $this
     */
    public function setDocument($document): self
    {
        return $this->setParam('document', $document);
    }

    /**
     * The index the document resides in.
     *
     * @return $this
     */
    public function setDocumentIndex(string $index): self
    {
        return $this->setParam('index', $index);
    }

    /**
     * The id of the document to fetch.
     *
     * @param int|string $id
     *
     * @return $this
     */
    public function setDocumentId($id): self
    {
        return $this->setParam('id', $id);
    }

    /**
     * Optionally, routing to be used to fetch document to percolate.
     *
     * @return $this
     */
    public function setDocumentRouting(string $routing): self
    {
        return $this->setParam('routing', $routing);
    }

    /**
     * Optionally, preference to be used to fetch document to percolate.
     *
     * @return $this
     */
    public function setDocumentPreference(array $preference): self
    {
        return $this->setParam('preference', $preference);
    }

    /**
     * Optionally, the expected version of the document to be fetched.
     *
     * @return $this
     */
    public function setDocumentVersion(int $version): self
    {
        return $this->setParam('version', $version);
    }
}
