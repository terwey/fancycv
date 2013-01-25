<?php

namespace fancycv;

class CategoryTest extends \PHPUnit_Framework_TestCase {
	private $_categoriesObject;

	protected function setUp() {

		$this->_categoriesObject = new Categories();
	}

	protected function tearDown() {
		unset($this->_categoriesObject);
	}

	function testNewCategoriesInstance() {
		$this->assertInstanceOf('fancycv\Categories', $this->_categoriesObject);
	}

	/**
	 * @depends testNewCategoriesInstance
	 **/
	function testNewCategory() {
		$name = 'support';
		$desc = 'Support blabla';
		$newCategory = $this->_categoriesObject->newCategory($name, $desc);
		$this->assertEquals($name, $newCategory);
		return $newCategory;
	}

	/**
	 * @depends testNewCategory
	 **/
	function testListCategories($newCategory) {
		$this->assertContains($newCategory, $this->_categoriesObject->listCategories());
	}

	/**
	 * @depends testNewCategoriesInstance
	 **/
	function testSaveCategories() {
		$this->assertTrue($this->_categoriesObject->save());
	}

	/**
	 * @depends testNewCategory
	 **/
	function testAddSkillToCategory($newCategory) {
		$skill = 'hacking';
		$this->assertTrue($this->_categoriesObject->addSkillToCategory($skill, $newCategory));
	}
}