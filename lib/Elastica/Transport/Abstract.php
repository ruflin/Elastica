<?php
/**
 * Elastica Abstract Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Transport_Abstract
{
    /**
     * Path
     *
     * @var string path
     */
    protected $_path;

    /**
     * Method
     *
     * @var string method
     * @todo set default method?
     */
    protected $_method;

    /**
     * Data
     *
     * @var array Data
     */
    protected $_data;

    /**
     * Config
     *
     * @var array config
     */
    protected $_config;

    /**
     * Construc transport
     *
     * @param Elastica_Request $request Request object
     */
    public function __construct(Elastica_Request $request)
    {
        $this->_request = $request;
    }

    /**
     * Returns the request object
     *
     * @return Elastica_Request Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Executes the transport request
     *
     * @param  array             $params Hostname, port, path, ...
     * @return Elastica_Response Response object
     */
    abstract public function exec(array $params);
}
