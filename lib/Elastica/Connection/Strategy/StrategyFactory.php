<?php

namespace Elastica\Connection\Strategy;

use Elastica\Exception\InvalidException;

class StrategyFactory
{
    /**
     * @var array
     */
    protected $_config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * @return StrategyInterface
     * @throws \Elastica\Exception\InvalidException
     */
    public function create()
    {
        $strategyClass = isset($this->_config['connectionPoolStrategy']) ? $this->_config['connectionPoolStrategy']: 'Simple';

        $fullClass = '\\Elastica\\Connection\\Strategy\\' . $strategyClass;

        if (class_exists($strategyClass)) {
            $strategy = new $strategyClass();
        } elseif (class_exists($fullClass)) {
            $strategy = new $fullClass();
        } else {
            throw new InvalidException("$strategyClass does not exists");
        }

        if (!$strategy instanceof StrategyInterface) {
            throw new InvalidException("$strategyClass should implement StrategyInterface");
        }

        return $strategy;
    }
}
