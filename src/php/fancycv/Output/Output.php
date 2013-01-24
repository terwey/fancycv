<?php
namespace fancycv\Output;

class Output
{
	protected $_formatObject;
    protected $_tableObject;
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
        $this->_tableObject = new Table($tableContents);
        return $this->_tableObject->table();
    }
}

?>
