<?php 

class Elastica_Filter_Query extends Elastica_Filter_Abstract {

    /**
     * @var array|Elastica_Query_Abstract 
     */
    protected $_query = null; 

    public function __construct($query = null) { 
        if(! is_null($query)) {
            $this->setQuery($query);
        } 
    } 

    public function setQuery($query) {
        if(! $query instanceof Elastica_Query_Abstract && ! is_array($query)) {
            throw new InvalidArgumentException('expected an array or instance of Elastica_Query_Abstract'); 
        } 
        $this->_query = $query; 
        return $this;
    } 

    public function toArray() {
        return array(
            'query' => ( 
                $this->_query instanceof Elastica_Query_Abstract 
                ? $this->_query->toArray()
                : $this->_query
            ),
        );
    } 

} 
