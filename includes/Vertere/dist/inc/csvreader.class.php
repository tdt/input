<?php

class CsvReader {
	var $_file;
	
	public function __construct($file) {
		$this->_file = $file;
	}
	
	public function next_record() {
		return fgetcsv($this->_file);
	}
}