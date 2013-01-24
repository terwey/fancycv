<?php
namespace fancycv\Output;

class Table extends Output
{
    protected $_table;
    public function __construct(array $tableContents) {
    	$this->_table = $tableContents;
    	return $this->table($tableContents);
    }

    public function table($tableContents) {
    	$table = '';
    	foreach ($this->_table as $rowContents) {
    		$table .= $this->row($rowContents);
    	}
    	return $table;
    }

    private function row(array $row) {
        var_dump($this);
    	$output = $this->getFormat()->rowOpen();
    	foreach ($row as $columns => $column) {
    		$output .= $this->column($column);
    	}
    	$output .= $this->getFormat()->rowClose();
    	return $output;
    }

    private function column($column) {
    	$output = '<td>';
    	$output .= $column;
    	$output .= '</td>';
    	return $output;
    }
}

?>
