<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Elastica index template object.
 *
 * @author Dmitry Balabka <dmitry.balabka@gmail.com>
 * 
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
 */
class IndexTemplate
{
    /**
     * Index template name.
     *
     * @var string Index pattern
     */
    protected $_name = '';

    /**
     * Client object.
     *
     * @var \Elastica\Client Client object
     */
    protected $_client = null;

    /**
     * Creates a new index template object.
     *
     * @param \Elastica\Client $client Client object
     * @param string           $name   Index template name
     *
     * @throws \Elastica\Exception\InvalidException
     */
    public function __construct(Client $client, $name)
    {
        $this->_client = $client;

        if (!is_scalar($name)) {
            throw new InvalidException('Index template should be a scalar type');
        }
        $this->_name = (string) $name;
    }

    /**
     * Deletes the index template.
     *
     * @return \Elastica\Response Response object
     */
    public function delete()
    {
        $response = $this->request(Request::DELETE);

        return $response;
    }

    /**
     * Creates a new index template with the given arguments.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
     *
     * @param array $args OPTIONAL Arguments to use
     *
     * @return \Elastica\Response
     */
    public function create(array $args = array())
    {
        return $this->request(Request::PUT, $args);
    }

    /**
     * Checks if the given index template is already created.
     *
     * @return bool True if index exists
     */
    public function exists()
    {
        $response = $this->request(Request::HEAD);
        $info = $response->getTransferInfo();

        return (bool) ($info['http_code'] == 200);
    }

    /**
     * Returns the index template name.
     *
     * @return string Index name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns index template client.
     *
     * @return \Elastica\Client Index client object
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Makes calls to the elasticsearch server based on this index template name.
     *
     * @param string $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array  $data   OPTIONAL Arguments as array
     *
     * @return \Elastica\Response Response object
     */
    public function request($method, $data = array())
    {
        $path = '/_template/'.$this->getName();

        return $this->getClient()->request($path, $method, $data);
    }
}
