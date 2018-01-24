<?php namespace dehydr8\Jdeserialize\content;

class EnumContent extends Content {

  public $classDescription;
  public $value;

  public function __construct($handle, $classDescription, $value) {
    parent::__construct($handle);
    $this->classDescription = $classDescription;
    $this->value = $value;
  }
  
  public function getType() {
    return "enum";
  }
}

?>