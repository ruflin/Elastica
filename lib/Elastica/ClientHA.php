<?php
/**
 * High Availability Client to connect to a cluster of elasticsearch servers. This class copes
 * with unreachable/failed nodes. An optional callback is triggered in case of node failures
 * and enables custom cache updates for server configurations outside the Elastica library.
 *
 * @package Elastica
 * @author Pascal von Rickenbach
 */
class Elastica_ClientHA extends Elastica_Client
{

    /**
     * Function to be called
     *
     * @var callback
     */
    protected $_callback = null;

    /**
     * Creates a new Elastica client
     *
     * @param array $config OPTIONAL Additional config options
     * @param callback|null $callback Callback to notify caller in case of unreachable servers
     */
    public function __construct(array $config = array(), $callback = null)
    {
        parent::__construct($config);
        $this->_callback = $callback;
    }

    /**
     * Returns an array of available servers
     *
     * @return array
     */
    public function getServers(){
        return $this->getConfig('servers');
    }

    /**
     * Disables an unreachable server and triggers registered callback. The function never
     * removes the last available server. So if the last server also goes down error messages
     * are triggered.
     *
     * @param array $disabled Configuration for unreachable server
     */
    public function disableServer(array $disabled){
        $servers = $this->getServers();

        // make sure we never disable the last server.
        if (count($servers) > 1) {
            // notify callback about failed server
            if (!is_null($this->_callback)) {
                call_user_func($this->_callback, $disabled);
            }
            $update = array();
            foreach($servers as $server) {
                if ($server['host'] == $disabled['host'] && $server['port'] === $disabled['port']) {
                    continue;
                }
                $update[] = $server;
            }

            $this->setConfigValue('servers', $update);
        }
    }

    /**
     * Makes calls to an elasticsearch server cluster based on this index
     *
     * It's possible to make any REST query directly over this method
     *
     * @param  string            $path   Path to call
     * @param  string            $method Rest method to use (GET, POST, DELETE, PUT)
     * @param  array             $data   OPTIONAL Arguments as array
     * @param  array             $query  OPTIONAL Query params
     * @return Elastica_Response Response object
     */
    public function request($path, $method, $data = array(), array $query = array())
    {
        $request = new Elastica_RequestHA($this, $path, $method, $data, $query);

        return $request->send();
    }

}
