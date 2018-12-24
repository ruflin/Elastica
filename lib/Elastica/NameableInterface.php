<?php

namespace Elastica;

/**
 * Interface for named objects.
 *
 *
 * @author Evgeniy Sokolov <ewgraf@gmail.com>
 */
interface NameableInterface
{
    /**
     * Retrieve the name of this object.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the name of this object.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);
}
