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
        $amountColumns = count($row);
        $count = 0;
    	$output = $this->_formatObject->rowOpen();
    	foreach ($row as $columns => $column) {
            $last = ($count == $amountColumns) ? TRUE : FALSE;
    		$output .= $this->column($column, $last);
    	}
    	$output .= $this->_formatObject->rowClose();
    	return $output;
    }

    private function column($column, $last=FALSE) {
        if ($this->_formatObject->join()) {
            $output = $column;
            $output .= ($last) ? '' : $this->_formatObject->columnJoin();
        } else {
        	$output = $this->_formatObject->columnOpen();
        	$output .= $column;
        	$output .= $this->_formatObject->columnClose();
        }
    	return $output;
    }
}

?>
