<?php

class SequenceGraph extends SimpleGraph {

	var $_collection_counts = array();

	public function add_bag_collection($subject) {
		$this->add_resource_triple($subject, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag');
		$this->_collection_counts[$subject] = 1;
	}

	public function add_sequence_collection($subject) {
		$this->add_resource_triple($subject, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq');
		$this->_collection_counts[$subject] = 1;
	}

	public function add_resource_to_collection($subject, $object) {
		$this->add_resource_triple($subject, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_'.$this->_collection_counts[$subject], $object);
		$this->_collection_counts[$subject] = $this->_collection_counts[$subject] + 1;
	}

	public function add_literal_to_collection($subject, $object, $lang = null, $dt = null) {
		$this->add_literal_triple($subject, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_'.$this->_collection_counts[$subject], $object, $lang, $dt);
		$this->_collection_counts[$subject] = $this->_collection_counts[$subject] + 1;
	}

}