<?php

class JAXB
{
	function __construct(){

	}

	// creating Class files
	function parseSchema($filename){
		$dom = new DomDocument();
		$dom->load($filename);

		// find all non simple types to create classes
		$elements = $dom->getElementsByTagName('element');
		foreach($elements as $element){
			if($element->childNodes->length > 1){
				$this->createClass($element);
			}
		}
	}

	// reading in XML data
	function unmarshal($filename){
		$this->filename = $filename;
		$dom = new DomDocument();
		$dom->load($filename);

		return $this->convertFromXML($dom->documentElement);
	}

	// writing out XML data
	function marshal($object,$filename=null){
		// if we did not receive a filename...
		if(!isset($filename)){
			// ... but we have one stored...
			if(isset($this->filename)){
				// then use the stored one
				$filename = $this->filename;
			}else{
				// otherwise quit out
				return false;
			}
		}


		$dom = new DomDocument();
		$dom->appendChild($this->convertToXML($object,$dom));

		echo "Writing to file $filename\n";
		$dom->formatOutput = true;
		return $dom->save($filename);
	}

	// 'parseSchema' helper method
	private function createClass($element){
		$name = $element->getAttribute('name');
		$attributeList = '';
		// skip the 'complexType' level
		foreach($element->childNodes->item(1)->childNodes as $child){
			if($child->nodeName == '#text'){
				continue;
			}elseif($child->localName == 'attribute'){
				$attrName = $child->getAttribute('name');
				$attributeList .= "\tprivate \$_$attrName;\n";
				$accessors .= "\tfunction get$attrName(){\n\t\treturn \$this->{_$attrName};\n\t}\n";
				$mutators .= "\tfunction set$attrName(\$value){\n\t\treturn \$this->{_$attrName} = \$value;\n\t}\n";
			}else{
				foreach($child->childNodes as $property){
					$attribute = '';
					if($property->nodeName == '#text'){
						continue;
					}elseif($property->hasAttribute('name')){
						$attrName = $property->getAttribute('name');
					}else{
						$attrName = $property->getAttribute('ref');
					}

					$attribute .= "\tprivate \$$attrName;\n";
					$accessors .= "\tfunction get$attrName(){\n\t\treturn \$this->$attrName;\n\t}\n";
					$mutators .= "\tfunction set$attrName(\$value){\n\t\treturn \$this->$attrName = \$value;\n\t}\n";

					$attributeList .= $attribute;
				}
			}
		}

		$string = <<<end
<?php

class $name
{
$attributeList
$accessors
$mutators
	function hasAttribute(\$attributeName){
		return property_exists(\$this, "_\$attributeName");
	}
}

?>
end;
		
		// create the file and log it
		if(!is_dir('classes')) mkdir('classes');
		file_put_contents("classes/$name.class.php", $string);
		echo "Writing to file $name.class.php\n";
	}

	// 'unmarshal' helper method
	private function convertFromXML($element){
		// require this element's class (made by the Schema parser)
		require_once("classes/{$element->localName}.class.php");
		$object = new $element->localName;
		// populate prpoerties from the attribute list of this element
		foreach($element->attributes as $name=>$value){
			if($object->hasAttribute($name)){
				$object->{"set$name"}($value->nodeValue);
			}
		}
		// populate properties from the child list of this element
		foreach($element->childNodes as $key=>$child){
			// forget about text elements
			if($child->nodeName == '#text'){
				continue;
			}
			// this means this guy is an object
			elseif($child->childNodes->length > 1){
				// recursion to create another object
				$attributeValue = $this->convertFromXML($child);
				// is this guy a member of an array, or does he stand alone as an object?
				// I had to check 2 places down in order to skip text nodes
				if($element->childNodes->item(intval($key)-2)->nodeName == $child->nodeName || $element->childNodes->item(intval($key)+2)->nodeName){
					// grab whats currently there, if it's an array then we just add our value,
					// if it's not an array then we start one, and if it's null then we just
					// add our value because it's the first one inserted
					$oldAttributeValue = $object->{"get{$child->nodeName}"}();
					if(gettype($oldAttributeValue) == 'array'){
						array_push($oldAttributeValue,$attributeValue);
						$attributeValue = $oldAttributeValue;
					}elseif($oldAttributeValue != null && $oldAttributeValue != ''){
						$attributeValue = array($oldAttributeValue,$attributeValue);
					}else{
						$attributeValue = array($attributeValue);
					}
				}
			}
			// this is just a normal property
			else{
				$attributeValue = $child->nodeValue;
			}

			$object->{"set{$child->nodeName}"}($attributeValue);
		}

		return $object;
	}

	// 'marshal' helper method
	private function convertToXML($object,$dom){
		// create the XML element
		$parent = $dom->createElement(get_class($object));
		// get all of the methods (accessors, mutators and hasAttribute) from this object's class
		$methods = get_class_methods(get_class($object));
		foreach($methods as $method){
			// filter down to only the accessors
			if(substr_compare($method, 'get', 0, 3) == 0){
				switch(gettype($object->{$method}())){
					// if this property is an array, loop through each object in the array
					// and convert it to an XML element
					case 'array':
						foreach($object->{$method}() as $child){
							$element = $this->convertToXML($child,$dom);
							// then append it to this XML element as a child
							$parent->appendChild($element);
						}
						break;
					// if this property is a string
					case 'string':
						// get the property name from the accessor method name
						$propertyName = substr($method, 3);
						// check to see if this property belongs as an attribute or
						// as a child node
						if($object->hasAttribute($propertyName)){
							$parent->setAttribute($propertyName,$object->{$method}());
						}else{
							$element = $dom->createElement($propertyName,$object->{$method}());
							$parent->appendChild($element);
						}
						break;
					case 'object':
						// convert the child to an XML element and append it
						$element = $this->convertToXML($object->{$method}(),$dom);
						$parent->appendChild($element);
						break;
				}
			}
		}
		return $parent;
	}
}

?>