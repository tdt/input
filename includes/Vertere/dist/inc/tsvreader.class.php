<?php

class TsvReader {
	var $_file;
	
	public function __construct($file) {
		$this->_file = $file;
	}
	
	public function next_record() {
		return fgetcsv($this->_file, 0, "\t");
	}
}