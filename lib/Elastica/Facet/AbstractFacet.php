<?php

namespace Elastica\Facet;

use Elastica\Exception\InvalidException;
use Elastica\Filter\AbstractFilter;
use Elastica\Param;

/**
 * Abstract facet object. Should be extended by all facet types
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Jasper van Wanrooy <jasper@vanwanrooy.net>
 */
abstract class AbstractFacet extends Param
{
    /**
     * Holds the name of the facet.
     * @var string
     */
    protected $_name = '';

    /**
     * Holds all facet parameters.
     * @var array
     */
    protected $_facet = array();

    /**
     * Constructs a Facet object.
     *
     * @param string $name The name of the facet.
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Sets the name of the facet. It is automatically set by
     * the constructor.
     *
     * @param  string                               $name The name of the facet.
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Facet\AbstractFacet
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new InvalidException('Facet name has to be set');
        }
        $this->_name = $name;

        return $this;
    }

    /**
     * Gets the name of the facet.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets a filter for this facet.
     *
     * @param  \Elastica\Filter\AbstractFilter $filter A filter to apply on the facet.
     * @return \Elastica\Facet\AbstractFacet
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->_setFacetParam('facet_filter', $filter->toArray());
    }

    /**
     * Sets the flag to either run the facet globally or bound to the
     * current search query. When not set, it defaults to the
     * Elasticsearch default value.
     *
     * @param  bool                          $global Flag to either run the facet globally.
     * @return \Elastica\Facet\AbstractFacet
     */
    public function setGlobal($global = true)
    {
        return $this->_setFacetParam('global', (bool) $global);
    }

    /**
     * Sets the path to the nested document
     *
     * @param  string                        $nestedPath Nested path
     * @return \Elastica\Facet\AbstractFacet
     */
    public function setNested($nestedPath)
    {
        return $this->_setFacetParam('nested', $nestedPath);
    }

    /**
     * Sets the scope
     *
     * @param  string                        $scope Scope
     * @return \Elastica\Facet\AbstractFacet
     */
    public function setScope($scope)
    {
        return $this->_setFacetParam('scope', $scope);
    }

    /**
     * Basic definition of all specs of the facet. Each implementation
     * should override this function in order to set it's specific
     * settings.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_facet;
    }

    /**
     * Sets a param for the facet. Each facet implementation needs to take
     * care of handling their own params.
     *
     * @param  string                        $key   The key of the param to set.
     * @param  mixed                         $value The value of the param.
     * @return \Elastica\Facet\AbstractFacet
     */
    protected function _setFacetParam($key, $value)
    {
        $this->_facet[$key] = $value;

        return $this;
    }
}
