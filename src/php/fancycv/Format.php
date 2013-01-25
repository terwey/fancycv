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
    private $_columnJoin;
    private $_join;

    public function __construct($format) {
    	$this->_formatName = $format;
        if (!file_exists(FORMATTERS.$format.'.yml')) {
            throw new \Exception('Format: "'. $format .'" does not exist!');
        } else {
            $formatter = Yaml::parse(FORMATTERS.$format.'.yml');
            $this->_tableOpen = $formatter['tableOpen'];
            $this->_tableClose = $formatter['tableClose'];
            $this->_rowOpen = $formatter['rowOpen'];
            $this->_rowClose = $formatter['rowClose'];
            $this->_columnOpen = $formatter['columnOpen'];
            $this->_columnClose = $formatter['columnClose'];
            $this->_columnJoin = $formatter['columnJoin'];
            $this->_join = $formatter['join'];
        }
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

    public function columnJoin() {
        return $this->_columnJoin;
    }

    public function join() {
        return $this->_join;
    }
}

?>
