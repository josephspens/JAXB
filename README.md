PHP JAXB
========

This is a plugin designed to make JAXB possible and easy in PHP.

JAXB
----

[JAXB](http://jaxb.java.net/) is a Java Architecture for XML Binding. As you can guess, it is native to the Java language. It is an alternative method for parsing XML documents from other techniques like DOM or SAX. There are two key steps in JAXB:

    1. Creating classes from an XML Schema
    2. Instantiating those classes using data from XML


### Under the Hood

By passing in the file path to an XML Schema, our JAXB plugin will read through the schema and pick out all the element definitions which have child elements. These are the XML elements which well will want to create into objects. Any SimpleTypes we can ignore. After that, it's a simple matter of creating a class file with the element's name, and assigning it attributes for every child element. Luckily PHP is a loosly-typed language, so we don't have to worry about data types. **fist pound**.

Running the `parse` method on a schema file should create a bunch of class files and stick them in the `classes` directory. The naming convention for class files is (using a class called 'Name' as an example) `Name.class.php`.

Next, we pass into the plugin the file path to an XML document. The plugin will then read through the XML file, creating objects and setting attributes when necessary. This plugin includes both XML attributes *and* child nodes as class properties. If the class property originated as an XML attribute, it will have an underscore before its name.

Example:

    <Company ID="2342" Name="Apple, Inc.">
        <Street>1 Infinite Loop</Street>
        <City>Cuppertino</City>
        <State>California</State>
        <Leader>Tim Cook</Leader>
    </Company>

Will produce the following object:

    class Company
    {
        private $_ID = "2342";
        private $_Name = "Apple, Inc.";
        private $Street = "1 Infinite Loop";
        private $City = "Cuppertino";
        private $State = "California";
        private $Leader = "Tim Cook";
    }

The script will also produce appropriate accessors and mutators, for instance `setName` and `getID` for the above example. I also added in a `hasAttribute` method which uses the `property_exists` PHP method, returning a boolean. Since the accessors and mutators ignore the underscore, you don't have to worry about it. It would just be used for reading in and out XML data, and if you wanted to modify the class file yourself. That's what's awesome about this technique, it creates iterable objects which you can modify and fine tune yourself.

The output from reading in an XML file is one large object, with nested objects within it representing the XML document tree.


FILES
-----

* [JAXB.class.php](https://github.com/josephspens/JAXB/blob/master/JAXB.class.php) -- converts a schema into class files, converts an XML document into an object
* [classes](https://github.com/josephspens/JAXB/blob/master/classes) -- folder holds all the outputted class files
* [sample.xml](https://github.com/josephspens/JAXB/blob/master/sample.xml) -- test input xml document
* [sample.xsd](https://github.com/josephspens/JAXB/blob/master/sample.xsd) -- test input schema
* [test.php](https://github.com/josephspens/JAXB/blob/master/test.php) -- our main script