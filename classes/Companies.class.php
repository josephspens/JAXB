<?php

class Companies
{
	private $Company;

	function getCompany(){
		return $this->Company;
	}

	function setCompany($value){
		return $this->Company = $value;
	}

	function hasAttribute($attributeName){
		return property_exists($this, "_$attributeName");
	}
}

?>