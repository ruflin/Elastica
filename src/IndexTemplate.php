<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Elastica index template object.
 *
 * @author Dmitry Balabka <dmitry.balabka@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
 */
class IndexTemplate
{
    /**
     * Index template name.
     *
     * @var string Index pattern
     */
    protected $_name;

    /**
     * @var Client
     */
    protected $_client;

    /**
     * Creates a new index template object.
     *
     * @param string $name Index template name
     *
     * @throws InvalidException
     */
    public function __construct(Client $client, $name)
    {
        $this->_client = $client;

        if (!\is_scalar($name)) {
            throw new InvalidException('Index template should be a scalar type');
        }
        $this->_name = (string) $name;
    }

    /**
     * Deletes the index template.
     *
     * @return Response
     */
    public function delete()
    {
        return $this->request(Request::DELETE);
    }

    /**
     * Creates a new index template with the given arguments.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
     *
     * @param array $args OPTIONAL Arguments to use
     *
     * @return Response
     */
    public function create(array $args = [])
    {
        return $this->request(Request::PUT, $args);
    }

    /**
     * Checks if the given index template is already created.
     *
     * @return bool
     */
    public function exists()
    {
        $response = $this->request(Request::HEAD);

        return 200 === $response->getStatus();
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
     * @return Client
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
     * @return Response
     */
    public function request($method, $data = [])
    {
        $path = '_template/'.$this->getName();

        return $this->getClient()->request($path, $method, $data);
    }
}
