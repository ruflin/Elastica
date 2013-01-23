<?php

namespace Elastica\Bulk\Action;

use Elastica\Document;

class CreateDocument extends IndexDocument
{
    /**
     * @var string
     */
    protected $_opType = Document::OP_TYPE_CREATE;
}
