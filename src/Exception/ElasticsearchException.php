<?php

namespace Elastica\Exception;

trigger_deprecation('ruflin/elastica', '5.2.0', 'The "%s" class is deprecated, use "Elastica\Exception\ResponseException::getResponse()::getFullError()" instead. It will be removed in 8.0.', ElasticsearchException::class);

/**
 * Elasticsearch exception.
 *
 * @author Ian Babrou <ibobrik@gmail.com>
 *
 * @deprecated since version 5.2.0
 */
class ElasticsearchException extends \Exception implements ExceptionInterface
{
    public const REMOTE_TRANSPORT_EXCEPTION = 'RemoteTransportException';

    /**
     * @var array Error array
     */
    protected $_error = [];

    /**
     * @var string|null Elasticsearch exception name
     */
    private $_exception;

    /**
     * @var bool Whether exception was local to server node or remote
     */
    private $_isRemote = false;

    /**
     * @param int    $code  Error code
     * @param string $error Error message from elasticsearch
     */
    public function __construct(int $code, string $error)
    {
        $this->_parseError($error);
        parent::__construct($error, $code);
    }

    /**
     * Returns elasticsearch exception name.
     *
     * @return string|null
     */
    public function getExceptionName()
    {
        return $this->_exception;
    }

    /**
     * Returns whether exception was local to server node or remote.
     */
    public function isRemoteTransportException(): bool
    {
        return $this->_isRemote;
    }

    /**
     * @return array Error array
     */
    public function getError(): array
    {
        return $this->_error;
    }

    /**
     * Parse error message from elasticsearch.
     *
     * @param string $error Error message
     */
    protected function _parseError(string $error): void
    {
        $errors = \explode(']; nested: ', $error);

        if (1 === \count($errors)) {
            $this->_exception = $this->_extractException($errors[0]);
        } else {
            if (self::REMOTE_TRANSPORT_EXCEPTION === $this->_extractException($errors[0])) {
                $this->_isRemote = true;
                $this->_exception = $this->_extractException($errors[1]);
            } else {
                $this->_exception = $this->_extractException($errors[0]);
            }
        }
    }

    /**
     * Extract exception name from error response.
     *
     * @return string|null
     */
    protected function _extractException(string $error)
    {
        if (\preg_match('/^(\w+)\[.*\]/', $error, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
