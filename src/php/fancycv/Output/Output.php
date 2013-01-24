<?php
namespace fancycv\Output;

class Output
{
	private $_format;
    public function __construct($format='tex') {
    	$this->_format = $format;
    }

    public function format() {
    	return $this->_format;
    }
}

?>
