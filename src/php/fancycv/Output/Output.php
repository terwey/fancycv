<?php
namespace fancycv\Output;

class Output
{
	private $_formatObject;
    private $_table;
    public function __construct($format='tex') {
    	$this->_formatObject = new Format($format);
    }

    public function getFormat() {
    	return $this->_formatObject;
    }

    public function setFormat($format) {
        $this->_formatObject = new Format($format);
    }

    public function table($tableContents) {
        return $this->_table = new Table($tableContents);
    }
}

?>
