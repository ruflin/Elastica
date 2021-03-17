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
            $predefinedStrategy = '\\Elastica\\Connection\\Strategy\\'.$strategyName;

            if (\class_exists($predefinedStrategy) && \is_subclass_of($predefinedStrategy, StrategyInterface::class)) {
                return new $predefinedStrategy();
            }

            if (\class_exists($strategyName) && \is_subclass_of($strategyName, StrategyInterface::class)) {
                return new $strategyName();
            }
        }

        throw new InvalidException('Can\'t create strategy instance by given argument');
    }
}
