<?php

namespace tdt\input\emlp\map;

abstract class AMapper{

	protected $mapper;

	public function __construct($mapper){
		$this->mapper = $mapper;
	}

    abstract public function execute(&$chunk);
}
