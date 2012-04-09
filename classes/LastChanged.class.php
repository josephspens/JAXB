<?php

class LastChanged
{
	private $ChangedBy;
	private $ChangedOn;

	function getChangedBy(){
		return $this->ChangedBy;
	}
	function getChangedOn(){
		return $this->ChangedOn;
	}

	function setChangedBy($value){
		return $this->ChangedBy = $value;
	}
	function setChangedOn($value){
		return $this->ChangedOn = $value;
	}

	function hasAttribute($attributeName){
		return property_exists($this, "_$attributeName");
	}
}

?>