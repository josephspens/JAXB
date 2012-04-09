<?php

class JAXB
{
	function __construct(){

	}

	function parse($filename){
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

	function unmarshal($filename){
		$dom = new DomDocument();
		$dom->load($filename);

		return $this->convert($dom->documentElement);
	}

	private function convert($element){
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
				$attributeValue = $this->convert($child);
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
		file_put_contents("classes/$name.class.php", $string);
		echo "Writing to file $name.class.php\n";
	}
}

?>