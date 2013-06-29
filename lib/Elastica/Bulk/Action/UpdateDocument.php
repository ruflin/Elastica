<?php

namespace Elastica\Bulk\Action;

use Elastica\Document;
use Elastica\Script;

/**
 * @package Elastica\Bulk\Action
 * @link http://www.elasticsearch.org/guide/reference/api/bulk/
 */
class UpdateDocument extends IndexDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_UPDATE;

    /**
     * Set the document for this bulk update action.
     * If the given Document object has a script, the script will be used in the update operation.
     * @param \Elastica\Document $document
     * @return \Elastica\Bulk\Action\UpdateDocument
     */
    public function setDocument(Document $document)
    {
        parent::setDocument($document);
        
        $source = array('doc' => $document->getData());
        
        if ($document->hasUpsert()) {
            $upsert = $document->getUpsert()->getData();
            
            if (!empty($upsert)) {
                $source['upsert'] = $upsert;
            }
        }
        
        $this->setSource($source);
        
        return $this;
    }
    
    /**
     * @param \Elastica\Script $script
     * @return \Elastica\Bulk\Action\UpdateDocument
     */
    public function setScript(Script $script)
    {
    	$this->_data = $script;
    	
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
    
    /**
     * @param \Elastica\Script|\Elastica\Document $data
     * @throws \InvalidArgumentException
     * @return \Elastica\Bulk\Action\UpdateDocument
     */
    public function setData($data)
    {
    	if ($data instanceof Script){
    		
    		$this->setScript($data);
    		
    	}else if ($data instanceof Document){
    		
    		$this->setDocument($data);
    		
    	}else{
    		throw new \InvalidArgumentException("Data should be a Document or a Script.");
    	}
    	
    	return $this;
    }
}
