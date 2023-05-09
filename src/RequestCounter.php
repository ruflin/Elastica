<?php declare(strict_types = 1);

namespace Elastica;

class RequestCounter implements RequestCounterInterface
{
    private int $count = 0;


    public function incrementCount(): void
    {
        ++$this->count;
    }


    public function getCount(): int
    {
        return $this->count;
    }
}
