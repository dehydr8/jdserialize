<?php 

class DeserializationTest extends PHPUnit_Framework_TestCase {
	
  /**
  * Just check if the Deserializer has no syntax error 
  */
  public function testIfTheresAnySyntaxError() {
    $var = new dehydr8\Jdeserialize\Deserializer("");
    $this->assertTrue(is_object($var));
    unset($var);
  }
  
}