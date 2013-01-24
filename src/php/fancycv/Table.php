<?php
namespace fancycv;

class Table
{
    private $_table;
    private $_formatObject;
    public function __construct(Format $formatObject, array $tableContents) {
    	$this->_table = $tableContents;
        $this->_formatObject = $formatObject;
    }

    public function table() {
    	$output = $this->_formatObject->tableOpen();
    	foreach ($this->_table as $rowContents) {
    		$output .= $this->row($rowContents);
    	}
        $output .= $this->_formatObject->tableClose();
    	return $output;
    }

    private function row(array $row) {
    	$output = $this->_formatObject->rowOpen();
    	foreach ($row as $columns => $column) {
    		$output .= $this->column($column);
    	}
    	$output .= $this->_formatObject->rowClose();
    	return $output;
    }

    private function column($column) {
    	$output = $this->_formatObject->columnOpen();
    	$output .= $column;
    	$output .= $this->_formatObject->columnClose();
    	return $output;
    }
}

?>
