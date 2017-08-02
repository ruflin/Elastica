<?php
namespace Bonami\Elastica\Exception\Bulk\Response;

use Bonami\Elastica\Bulk\Response;
use Bonami\Elastica\Exception\BulkException;

class ActionException extends BulkException
{
    /**
     * @var \Bonami\Elastica\Response
     */
    protected $_response;

    /**
     * @param \Bonami\Elastica\Bulk\Response $response
     */
    public function __construct(Response $response)
    {
        $this->_response = $response;

        parent::__construct($this->getErrorMessage($response));
    }

    /**
     * @return \Bonami\Elastica\Bulk\Action
     */
    public function getAction()
    {
        return $this->getResponse()->getAction();
    }

    /**
     * @return \Bonami\Elastica\Bulk\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param \Bonami\Elastica\Bulk\Response $response
     *
     * @return string
     */
    public function getErrorMessage(Response $response)
    {
        $error = $response->getError();
        $opType = $response->getOpType();
        $data = $response->getData();

        $path = '';
        if (isset($data['_index'])) {
            $path .= '/'.$data['_index'];
        }
        if (isset($data['_type'])) {
            $path .= '/'.$data['_type'];
        }
        if (isset($data['_id'])) {
            $path .= '/'.$data['_id'];
        }
        $message = "$opType: $path caused $error";

        return $message;
    }
}
