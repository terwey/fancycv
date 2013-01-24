<?php
namespace fancycv;
use Symfony\Component\Yaml\Yaml;

class Format
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
        $formatter = Yaml::parse(FORMATTERS.$format.'.yml');
        $this->_tableOpen = $formatter['tableOpen']."\n";
        $this->_tableClose = $formatter['tableClose']."\n";
        $this->_rowOpen = $formatter['rowOpen']."\n";
        $this->_rowClose = $formatter['rowClose']."\n";
        $this->_columnOpen = $formatter['columnOpen']."\n";
        $this->_columnClose = $formatter['columnClose']."\n";
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
