<?php

namespace fancycv;

class OutputTest extends \PHPUnit_Framework_TestCase {
	private $_formatObject;

	protected function setUp() {
		$format = 'tex';
		$this->_formatObject = new Format($format);
	}

	protected function tearDown() {
		unset($this->_formatObject);
	}

	function testNewFormatInstance() {
		$this->assertInstanceOf('fancycv\Format', $this->_formatObject);
	}

	function testNewOutputTable() {
		$tableContents = array(
			array( // row 1
				'row1_Column1', // column1
				'row1_Column2' // column2
			),
			array( // row 2
				'row2_Column1', // column1
				'row2_Column2' // column2
			)
		);
		$table = new Table($this->_formatObject, $tableContents);
		$this->assertInstanceOf('fancycv\Table', $table);
		print $table->table();
	}
}