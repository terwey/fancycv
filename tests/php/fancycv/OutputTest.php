<?php

namespace fancycv\Output;

class OutputTest extends \PHPUnit_Framework_TestCase {
	private $_output;

	protected function setUp() {
		$format = 'tex';
		$this->_output = new Output($format);
		$this->assertEquals($format, $this->_output->Format());
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
		$table = new Table($tableContents);
		$this->assertInstanceOf('fancycv\Output\Table', $table);
		return $table;
	}

	/**
	* @depends testNewOutputTable
	**/
	function testOutputTableRow($table) {
		$table->row();
	}

	/**
	* @depends testTableOutput
	**/
	function testTableRowOutput($table) {
		$table = $this->output->row();
	}
}