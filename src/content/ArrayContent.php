<?php namespace dehydr8\Jdeserialize\content;

class ArrayContent extends Content {

  public $data;
  public $class;

  public function __construct($handle, $class, $data) {
    parent::__construct($handle);
    $this->class = $class;
    $this->data = $data;
  }
  
  public function getType() {
    return "array-content";
  }
}

?>