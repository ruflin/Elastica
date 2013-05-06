<?php

namespace Elastica;

/**
 * Elastica document object interface
 *
 * This interface can be implemented by entities that will be persisted as an Elastica Document
 *
 * *WARNING* This interface is EXPERIMENTAL and will likely be changed/expanded in the future
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Lukas Kahwe Smith <smith@pooteeweet.org>
 */
interface DocumentObjectInterface
{
    /**
     * Get the elastica ID
     *
     * @return string The id to use when persisting this entity
     */
    public function getElasticaDocumentId();
}
