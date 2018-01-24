<?php namespace dehydr8\Jdeserialize\content;

class Instance extends Content {

  public $classDescription;
  public $fieldData = array();
  public $annotations = array();

  public function __construct($handle) {
    parent::__construct($handle);
  }
  
  public function getType() {
    return "instance";
  }
}
?>