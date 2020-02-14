<?php

namespace Elastica\Connection\Strategy;

use Elastica\Exception\InvalidException;

/**
 * Description of StrategyFactory.
 *
 * @author chabior
 */
class StrategyFactory
{
    /**
     * @param callable|mixed|StrategyInterface|string $strategyName
     *
     * @throws InvalidException
     */
    public static function create($strategyName): StrategyInterface
    {
        if ($strategyName instanceof StrategyInterface) {
            return $strategyName;
        }

        if (\is_callable($strategyName)) {
            return new CallbackStrategy($strategyName);
        }

        if (\is_string($strategyName)) {
            $requiredInterface = StrategyInterface::class;
            $predefinedStrategy = '\\Elastica\\Connection\\Strategy\\'.$strategyName;

            if (\class_exists($predefinedStrategy) && \class_implements($predefinedStrategy, $requiredInterface)) {
                return new $predefinedStrategy();
            }

            if (\class_exists($strategyName) && \class_implements($strategyName, $requiredInterface)) {
                return new $strategyName();
            }
        }

        throw new InvalidException('Can\'t create strategy instance by given argument');
    }
}
