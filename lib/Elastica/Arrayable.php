<?php
namespace Elastica;

/**
 * Interface for params.
 *
 *
 * @author Evgeniy Sokolov <ewgraf@gmail.com>
 */
interface Arrayable
{
    /**
     * Converts the object to an array.
     *
     * @return array Object as array
     */
    public function toArray();
}
