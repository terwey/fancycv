<?php
namespace fancycv;
use Symfony\Component\Yaml\Yaml;

class Format
{
    private $_formatName;

    // format vars
    private $_documentOpen;
    private $_documentClose;
    private $_sectionOpen;
    private $_sectionClose;

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
            $this->_documentOpen = $formatter['documentOpen'];
            $this->_documentClose = $formatter['documentClose'];
            $this->_sectionOpen = $formatter['sectionOpen'];
            $this->_sectionClose = $formatter['sectionClose'];

            // table related formatting
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

    public function documentOpen() {
        return $this->_documentOpen;
    }

    public function documentClose() {
        return $this->_documentClose;
    }

    public function sectionOpen() {
        return $this->_sectionOpen;
    }

    public function sectionClose() {
        return $this->_sectionClose;
    }

    // small helper function
    public function sectionTitle($title) {
        return $this->sectionOpen(). $title . $this->sectionClose();
    }

    // table related functions
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
