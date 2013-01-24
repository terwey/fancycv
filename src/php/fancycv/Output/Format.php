<?php
namespace fancycv\Output;

class Format extends Output
{
    private $_formatName;

    // format vars
    // table
    private $_tableOpen;
    private $_tableClose;
    private $_rowOpen;
    private $_rowClose;
    private $_columnOpen;
    private $_columnClose;

    public function __construct($format) {
    	$this->_formatName = $format;
    }

    public function getFormatName() {
        return $this->_formatName;
    }

    public function tableOpen() { 
        return $this->_tableOpen; 
    }

    public function tableClose() {
        return $this->_tableClose;
    }

    public function rowOpen() { 
        return $this->_rowOpen; 
    }

    public function rowClose() { 
        return $this->_rowClose;
    }

    public function columnOpen() { 
        return $this->_columnOpen;
    }

    public function columnClose() { 
        return $this->_columnClose;
    }

}

?>
