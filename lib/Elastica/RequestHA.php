<?php
/**
 * Elastica High Availability Request object. This class re-send the request to
 * other servers of an elasticsearch cluster in case of node failures.
 *
 * @package Elastica
 * @author  Pascal von Rickenbach
 */
class Elastica_RequestHA extends Elastica_Request
{

    /**
     * High Availability Client
     *
     * @var Elastica_ClientHA Client object
     */
    protected $_client;

    /**
     * Construct
     *
     * @param Elastica_ClientHA $client
     * @param string          $path   Request path
     * @param string          $method Request method (use const's)
     * @param array           $data   OPTIONAL Data array
     * @param array           $query  OPTIONLA Query params
     */
    public function __construct( Elastica_ClientHA $client, $path, $method, $data = array(), array $query = array() )
    {
        parent::__construct( $client, $path, $method, $data, $query );

        $this->_client = $client;
    }

    /**
     * Return High Availability Client Object
     *
     * @return Elastica_ClientHA
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Sends request to server. Retries other servers in case of node failures.
     *
     * @return Elastica_Response Response object
     */
    public function send()
    {
        $log = new Elastica_Log( $this->getClient() );
        $log->log( $this );

        $transport = $this->getTransport();

        $servers = $this->getClient()->getServers();

        // use normal Elastica_Client if no servers are specified in configuration
        if (empty( $servers )) {
            throw new Elastica_Exception_NotImplemented();
        }

        // precaution to avoid infinite loop
        $trials = count( $servers );
        while ($trials > 0) {

            // Set server id for first request (round robin by default)
            if (is_null( self::$_serverId )) {
                self::$_serverId = rand( 0, count( $servers ) - 1 );
            } else {
                self::$_serverId = ( self::$_serverId + 1 ) % count( $servers );
            }

            $server = $servers[self::$_serverId];

            try {
                return $transport->exec( $server );
            } catch( Elastica_Exception_Client $exception ) {
                if ($exception->getError() === CURLE_COULDNT_CONNECT && count( $servers ) > 1) {
                    // notify client about failed node
                    $this->getClient()->disableServer( $server );
                    // update server list
                    $servers = $this->getClient()->getServers();
                    $trials--;
                } else {
                    throw $exception;
                }
            }
        }
        // never reached
    }
}
