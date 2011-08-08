<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ParamTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests if filter name is set correct and instance is created
	 */
	public function testInstance() {
		$className = 'Elastica_ParamAbstract';
		$param = $this->getMock('Elastica_Param', null, array(), $className);

		$this->assertInstanceOf('Elastica_Param', $param);
		$this->assertEquals(array('param_abstract' => array()), $param->toArray());
	}

	public function testToArrayEmpty() {
		$param = new Elastica_Param();
		$this->assertInstanceOf('Elastica_Param', $param);
		$this->assertEquals(array($this->_getFilterName($param) => array()), $param->toArray());
	}

	public function testSetParams() {
		$param = new Elastica_Param();
		$params = array('hello' => 'word', 'nicolas' => 'ruflin');
		$param->setParams($params);

		$this->assertInstanceOf('Elastica_Param', $param);
		$this->assertEquals(array($this->_getFilterName($param) => $params), $param->toArray());
	}

	public function testSetGetParam() {
		$param = new Elastica_Param();

		$key = 'name';
		$value = 'nicolas ruflin';

		$params = array($key => $value);
		$param->setParam($key, $value);

		$this->assertEquals($params, $param->getParams());
		$this->assertEquals($value, $param->getParam($key));
	}

	public function testAddParam() {
		$param = new Elastica_Param();

		$key = 'name';
		$value = 'nicolas ruflin';

		$param->addParam($key, $value);

		$this->assertEquals(array($key => array($value)), $param->getParams());
		$this->assertEquals(array($value), $param->getParam($key));
	}

	public function testAddParam2() {
		$param = new Elastica_Param();

		$key = 'name';
		$value1 = 'nicolas';
		$value2 = 'ruflin';

		$param->addParam($key, $value1);
		$param->addParam($key, $value2);

		$this->assertEquals(array($key => array($value1, $value2)), $param->getParams());
		$this->assertEquals(array($value1, $value2), $param->getParam($key));
	}

	public function testGetParamInvalid() {
		$param = new Elastica_Param();

		try {
			$param->getParam('notest');
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