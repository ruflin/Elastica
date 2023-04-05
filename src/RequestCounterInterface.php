<?php declare(strict_types = 1);

namespace Elastica;

interface RequestCounterInterface
{
    public function incrementCount(): void;

    public function getCount(): int;
}
