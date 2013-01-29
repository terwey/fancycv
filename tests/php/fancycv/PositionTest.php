<?php

namespace fancycv;

class PositionTest extends \PHPUnit_Framework_TestCase {
	private $_positionsObject;

	protected function setUp() {

		$this->_positionsObject = new Positions();
	}

	protected function tearDown() {
		unset($this->_positionsObject);
	}

	function testNewTypesInstance() {
		$this->assertInstanceOf('fancycv\Positions', $this->_positionsObject);
	}

	/**
	 * @depends testNewTypesInstance
	 **/
	function testNewType() {
		$name = 'Self Employed';
		$desc = 'Companies I worked for under contract';
		$this->assertTrue($this->_positionsObject->newType($name, $desc));
		return $name;
	}

	/**
	 * @depends testNewType
	 **/
	function testListTypes($typeName) {
		$this->assertContains($typeName, $this->_positionsObject->listTypes());
	}

	/**
	 * @depends testNewTypesInstance
	 **/
	function testSaveTypes() {
		$this->assertTrue($this->_positionsObject->save());
	}

	/**
	 * @depends testNewType
	 **/
	function testAddPositionToType($typeName) {
		$periodFrom = 'Feb 2010';
		$periodTo = 'Present';
		$employer = 'Some Inc';
		$title = 'Software Developer';
		$summary = 'Some long ass text that describes how great I was at my job and whatnot. Also explains that my co-workers were a bunch of idiots :p';
		$skills = NULL;
		$this->assertTrue($this->_positionsObject->addPositionToType($typeName, $periodFrom, $periodTo, $employer, $title, $summary, $skills), 'Tests adding a new Position');
		$this->assertTrue($this->_positionsObject->addPositionToType($typeName, $periodFrom, $periodTo, 'Some Corp', $title, $summary, $skills), 'Tests adding a second Position');
		return $typeName;
	}

	/**
	 * @depends testAddPositionToType
	 **/
	function testDeletePositionFromType($typeName) {
		$position = 'Some Inc';
		$this->assertTrue($this->_positionsObject->deletePositionFromType($position, $typeName));
	}

	/**
	 * @depends testAddPositionToType
	 **/
	function testDeleteType($typeName) {
		// don't delete Type if there are Positions present
		$this->assertFalse($this->_positionsObject->deleteType($typeName, FALSE));
		// always delete Type
		$this->assertTrue($this->_positionsObject->deleteType($typeName, TRUE));
	}

	function testMovePosition() {
		$typeName = 'Self Employed';
		$typeNameTarget = 'Fixed';
		$periodFrom = 'Feb 2010';
		$periodTo = 'Present';
		$employer = 'Some Company';
		$title = 'Software Developer';
		$summary = 'Some long ass text that describes how great I was at my job and whatnot. Also explains that my co-workers were a bunch of idiots :p';
		$skills = NULL;
		$this->assertTrue($this->_positionsObject->newType($typeName), 'Create a Type where a Test Position can be added to.');
		$this->assertTrue($this->_positionsObject->addPositionToType($typeName, $periodFrom, $periodTo, $employer, $title, $summary, $skills), 'Adds a new Position that can be moved.');
		$this->assertTrue($this->_positionsObject->newType($typeNameTarget), 'Created the new Target Type');
		$this->assertTrue($this->_positionsObject->movePositionToType($employer, $typeName, $typeNameTarget), 'Tests moving a positions.');
		return $employer;
	}

	
	/**
	 * @depends testMovePosition
	 **/
	function testGetPosition($employer) {
		$this->assertContains($employer, $this->_positionsObject->getPosition($employer));
	}	
	/**
	 * @depends testMovePosition
	 **/
	function testAddSkillToPosition($employer) {
		$skill = array('Hacking' => 'Poking stuff');
		$this->assertTrue($this->_positionsObject->addSkillToPosition($employer, $skill));
		return $employer;
	}

	/**
	 * @depends testAddSkillToPosition
	 **/
	function testGetSkillsFromPosition($employer) {
		$this->assertArrayHasKey('Hacking', $this->_positionsObject->getSkillsFromPosition($employer));
		return $employer;
	}

	/**
	 * @depends testGetSkillsFromPosition
	 **/
	function testDeleteSkillFromPosition($employer) {
		$skillName = 'Hacking';
		$this->assertTrue($this->_positionsObject->deleteSkillFromPosition($employer, $skillName));
	}
}