<?php
/**
 * Response exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Exception_Response extends Elastica_Exception {

    protected $_response = null;
    
    public function __construct(Elastica_Response $response) {
        $this->_response = $response;        
        parent::__construct($response->getError());
    }

    public function getResponse() {
        return $this->_response;
    }
}
