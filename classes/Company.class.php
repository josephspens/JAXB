<?php

class Company
{
	private $Name;
	private $Salesman;
	private $Terms;
	private $RatePlan;
	private $ContractNumber;
	private $LastChanged;
	private $_ID;

	function getName(){
		return $this->Name;
	}
	function getSalesman(){
		return $this->Salesman;
	}
	function getTerms(){
		return $this->Terms;
	}
	function getRatePlan(){
		return $this->RatePlan;
	}
	function getContractNumber(){
		return $this->ContractNumber;
	}
	function getLastChanged(){
		return $this->LastChanged;
	}
	function getID(){
		return $this->{_ID};
	}

	function setName($value){
		return $this->Name = $value;
	}
	function setSalesman($value){
		return $this->Salesman = $value;
	}
	function setTerms($value){
		return $this->Terms = $value;
	}
	function setRatePlan($value){
		return $this->RatePlan = $value;
	}
	function setContractNumber($value){
		return $this->ContractNumber = $value;
	}
	function setLastChanged($value){
		return $this->LastChanged = $value;
	}
	function setID($value){
		return $this->{_ID} = $value;
	}

	function hasAttribute($attributeName){
		return property_exists($this, "_$attributeName");
	}
}

?>