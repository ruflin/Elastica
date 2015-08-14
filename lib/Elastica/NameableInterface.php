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
    public function getName();

    public function setName($name);
}
