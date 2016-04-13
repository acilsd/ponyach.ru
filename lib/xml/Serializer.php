<?php
abstract class XmlSerializer {
    private static $dom;
	
    private static function init($rootNode){
		self::$dom = new DomDocument("1.0", "UTF-8");
		$root = self::$dom->createElement($rootNode);
		self::$dom->appendChild($root);
		self::$dom->formatOutput = true;
    }
	
	private static function get_class_name($object)
	{
		$class = get_class($object);
		if (!$class) {
			return "object";
		}
		$class = explode('\\', get_class($object));
		return $class[count($class) - 1];
	}

	/**
	 * Nur für Objekte!
	 */
    private static function iteratechildren($object, $xml){
        foreach ($object as $name => $value) {
            if (is_string($value) || is_numeric($value)) {
				$node = self::$dom->createElement($name);
				$textNode = self::$dom->createTextNode($value);
				$node->appendChild($textNode);
				$xml->appendChild($node);
            } 
			elseif (!isset($value)) {
				$node = self::$dom->createElement($name);
				$xml->appendChild($node);
			}
			else {
				$className = self::get_class_name($value);
				$node = self::$dom->createElement($className);
				$xml->appendChild($node);
                self::iteratechildren($value, $node);
            }
        }
    }
    
    public static function toXml($object, $rootNode = 'root') {
		self::init($rootNode);
		
		// Felder eines Objektes rekursiv nach Feldnamen auflösen
		if (is_object($object) || is_array($object)) {
			self::iteratechildren($object, self::$dom->documentElement);
		} 
		
		// Nur Typname und Wert ausgeben
		elseif (is_string($object) || is_numeric($object)) {
			$node = self::$dom->createElement(gettype($object), $object);
			self::$dom->documentElement->appendChild($node);
		}
		
        return self::$dom->saveXML();
    }
}