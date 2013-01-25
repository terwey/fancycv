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
        $newline = ($this->_tableOpen['newline']) ? "\n" : '';
        return $this->_tableOpen['code'].$newline;
    }

    public function tableClose() {
        $newline = ($this->_tableClose['newline']) ? "\n" : '';
        return $this->_tableClose['code'].$newline;
    }

    public function rowOpen() {
        $newline = ($this->_rowOpen['newline']) ? "\n" : '';
        return $this->_rowOpen['code'].$newline;
    }

    public function rowClose() {
        $newline = ($this->_rowClose['newline']) ? "\n" : '';
        return $this->_rowClose['code'].$newline;
    }

    public function columnOpen() { 
        $newline = ($this->_columnOpen['newline']) ? "\n" : '';
        return $this->_columnOpen['code'].$newline;
    }

    public function columnClose() {
        $newline = ($this->_columnClose['newline']) ? "\n" : '';
        return $this->_columnClose['code'].$newline;
    }

    public function columnJoin() {
        $newline = ($this->_columnJoin['newline']) ? "\n" : '';
        return $this->_columnJoin['code'].$newline;
    }

    public function join() {
        return $this->_join;
    }
}

?>
