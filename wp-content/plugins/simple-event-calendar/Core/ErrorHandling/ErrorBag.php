<?php

namespace GDCalendar\Core\ErrorHandling;

class ErrorBag extends \Exception {

	protected $errorMessages = array();

	public function addError($errorMessage){
		$this->errorMessages[] = $errorMessage;
	}

	public function getErrors(){
		return $this->errorMessages;
	}

	public function hasErrors(){
		return !!empty($this->errorMessages);
	}

}