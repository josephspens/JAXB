<?php

class Schema
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