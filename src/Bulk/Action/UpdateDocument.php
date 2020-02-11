<?php

namespace Elastica\Bulk\Action;

use Elastica\Document;
use Elastica\Script\AbstractScript;

class UpdateDocument extends IndexDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_UPDATE;

    /**
     * {@inheritdoc}
     */
    public function setDocument(Document $document): AbstractDocument
    {
        parent::setDocument($document);

        $source = ['doc' => $document->getData()];

        if ($document->getDocAsUpsert()) {
            $source['doc_as_upsert'] = true;
        } elseif ($document->hasUpsert()) {
            $upsert = $document->getUpsert()->getData();

            if (!empty($upsert)) {
                $source['upsert'] = $upsert;
            }
        }

        $this->setSource($source);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setScript(AbstractScript $script): AbstractDocument
    {
        parent::setScript($script);

        // FIXME: can we throw away toArray cast?
        $source = $script->toArray();

        if ($script->hasUpsert()) {
            $upsert = $script->getUpsert()->getData();

            if (!empty($upsert)) {
                $source['upsert'] = $upsert;
            }
        }

        $this->setSource($source);

        return $this;
    }
}
