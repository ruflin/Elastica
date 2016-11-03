<?php
namespace Elastica\Query;

/**
 * Percolate query.
 *
 * @author Boris Popovschi <zyqsempai@mail.ru>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-percolate-query.html
 */
/**
 * Class Percolate.
 */
class Percolate extends AbstractQuery
{
    /**
     * The field of type percolator and that holds the indexed queries. This is a required parameter.
     *
     * @param $field
     *
     * @return $this
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * The source of the document being percolated.
     *
     * @param $document
     *
     * @return $this
     */
    public function setDocument($document)
    {
        return $this->setParam('document', $document);
    }

    /**
     * The type / mapping of the document being percolated. This is a required parameter.
     *
     * @param $documentType
     *
     * @return $this
     */
    public function setDocumentType($documentType)
    {
        return $this->setParam('document_type', $documentType);
    }

    /**
     * The index the document resides in.
     *
     * @param $index
     *
     * @return $this
     */
    public function setDocumentIndex($index)
    {
        return $this->setParam('index', $index);
    }

    /**
     * The type of the document to fetch.
     *
     * @param $type
     *
     * @return $this
     */
    public function setExistingDocumentType($type)
    {
        return $this->setParam('type', $type);
    }

    /**
     * The id of the document to fetch.
     *
     * @param $id
     *
     * @return $this
     */
    public function setDocumentId($id)
    {
        return $this->setParam('id', $id);
    }

    /**
     * Optionally, routing to be used to fetch document to percolate.
     *
     * @param $routing
     *
     * @return $this
     */
    public function setDocumentRouting($routing)
    {
        return $this->setParam('routing', $routing);
    }

    /**
     * Optionally, preference to be used to fetch document to percolate.
     *
     * @param $preference
     *
     * @return $this
     */
    public function setDocumentPreference($preference)
    {
        return $this->setParam('preference', $preference);
    }

    /**
     * Optionally, the expected version of the document to be fetched.
     *
     * @param $version
     *
     * @return $this
     */
    public function setDocumentVersion($version)
    {
        return $this->setParam('version', $version);
    }
}
