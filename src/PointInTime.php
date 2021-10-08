<?php

namespace Elastica;

/**
 * Implementation of Point-In-Time for the Search request.
 */
class PointInTime extends Param
{
    public function __construct(string $id, string $keepAlive)
    {
        $this->setParams(['id' => $id, 'keep_alive' => $keepAlive]);
    }

    public function toArray(): array
    {
        $data = $this->getParams();

        if ($this->_rawParams) {
            $data = \array_merge($data, $this->_rawParams);
        }

        return $this->_convertArrayable($data);
    }
}
