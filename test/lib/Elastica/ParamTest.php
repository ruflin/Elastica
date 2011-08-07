<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ParamTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests if filter name is set correct and instance is created
	 */
	public function testInstance() {
		$className = 'Elastica_ParamAbstract';
		$filter = $this->getMockForAbstractClass('Elastica_Param', array(), $className);

		$this->assertInstanceOf('Elastica_Param', $filter);
		$this->assertEquals(array('param_abstract' => array()), $filter->toArray());
	}

	public function testToArrayEmpty() {
		$filter = $this->getMockForAbstractClass('Elastica_Param');
		$this->assertInstanceOf('Elastica_Param', $filter);
		$this->assertEquals(array($this->_getFilterName($filter) => array()), $filter->toArray());
	}

	public function testSetParams() {
		$filter = $this->getMockForAbstractClass('Elastica_Param');
		$params = array('hello' => 'word', 'nicolas' => 'ruflin');
		$filter->setParams($params);

		$this->assertInstanceOf('Elastica_Param', $filter);
		$this->assertEquals(array($this->_getFilterName($filter) => $params), $filter->toArray());
	}

	public function testSetGetParam() {
		$filter = $this->getMockForAbstractClass('Elastica_Param');

		$key = 'name';
		$value = 'nicolas ruflin';

		$params = array($key => $value);
		$filter->setParam($key, $value);

		$this->assertEquals($params, $filter->getParams());
		$this->assertEquals($value, $filter->getParam($key));
	}

	public function testAddParam() {
		$filter = $this->getMockForAbstractClass('Elastica_Param');

		$key = 'name';
		$value = 'nicolas ruflin';

		$filter->addParam($key, $value);

		$this->assertEquals(array($key => array($value)), $filter->getParams());
		$this->assertEquals(array($value), $filter->getParam($key));
	}

	public function testAddParam2() {
		$filter = $this->getMockForAbstractClass('Elastica_Param');

		$key = 'name';
		$value1 = 'nicolas';
		$value2 = 'ruflin';

		$filter->addParam($key, $value1);
		$filter->addParam($key, $value2);

		$this->assertEquals(array($key => array($value1, $value2)), $filter->getParams());
		$this->assertEquals(array($value1, $value2), $filter->getParam($key));
	}

	public function testGetParamInvalid() {
		$filter = $this->getMockForAbstractClass('Elastica_Param');

		try {
			$filter->getParam('notest');
			$this->fail('Should throw exception');
		} catch(Elastica_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	protected function _getFilterName($filter) {
		// Picks the last part of the class name and makes it snake_case
		$classNameParts = explode('_', get_class($filter));
		return Elastica_Util::toSnakeCase(array_pop($classNameParts));
	}
}