<?php

namespace fancycv\Output;

class OutputTest extends \PHPUnit_Framework_TestCase {
	private $_output;

	protected function setUp() {
		$format = 'tex';
		$this->_output = new Output($format);
	}

	protected function tearDown() {
		unset($this->_output);
	}

	function testNewOutput() {
		$this->assertInstanceOf('fancycv\Output\Output', $this->_output);
	}

	/**
	* @depends testNewOutput
	**/
	function testNewOutputFormat() {
		$this->assertInstanceOf('fancycv\Output\Format', $this->_output->getFormat());
	}

	/**
	* @depends testNewOutput
	**/
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
		var_dump($this->_output);
		$table = $this->_output->table($tableContents);
		$this->assertInstanceOf('fancycv\Output\Table', $table);
	}
}