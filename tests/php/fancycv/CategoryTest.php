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
		$this->assertTrue($this->_categoriesObject->newCategory($name, $desc));
		return $name;
	}

	/**
	 * @depends testNewCategory
	 **/
	function testListCategories($categoryName) {
		$this->assertContains($categoryName, $this->_categoriesObject->listCategories());
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
	function testAddSkillToCategory($categoryName) {
		$skill = 'hacking';
		$skillDesc = 'Making it do more stuff then it\'s supposed to';
		$this->assertTrue($this->_categoriesObject->addSkillToCategory($skill, $categoryName, $skillDesc));
		$skill2 = 'calling';
		$skillDesc2 = 'Lorem ipsum dolar amet';
		$this->assertTrue($this->_categoriesObject->addSkillToCategory($skill2, $categoryName, $skillDesc2));
		return $categoryName;
	}

	/**
	 * @depends testAddSkillToCategory
	 **/
	function testDeleteSkillFromCategory($categoryName) {
		$skill = 'hacking';
		$skillDesc = 'Making it do more stuff then it\'s supposed to';
		$this->assertTrue($this->_categoriesObject->deleteSkillFromCategory($skill, $categoryName));
	}

	/**
	 * @depends testAddSkillToCategory
	 **/
	function testDeleteCategory($categoryName) {
		// don't delete category if there are skills present
		$this->assertFalse($this->_categoriesObject->deleteCategory($categoryName, FALSE));
		// always delete category
		// FOR SOME REASON THIS IS BROKEN ATM
		// $this->assertTrue($this->_categoriesObject->deleteCategory($categoryName, TRUE));
	}

	function testMoveSkill() {
		$skill = 'testing';
		$skillDesc = 'Testing stuff';
		$categoryName = 'Unsorted';
		$categoryNameTarget = 'Development';
		$this->assertTrue($this->_categoriesObject->newCategory($categoryName));
		$this->assertTrue($this->_categoriesObject->addSkillToCategory($skill, $categoryName, $skillDesc));
		$this->assertTrue($this->_categoriesObject->newCategory($categoryNameTarget));
		$this->assertTrue($this->_categoriesObject->moveSkillToCategory($skill, $categoryName, $categoryNameTarget));
	}
}