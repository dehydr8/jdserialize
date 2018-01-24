<?php namespace dehydr8\Jdeserialize\content;

class StringContent extends Content {

  public $data;

  public function __construct($handle, $data) {
    parent::__construct($handle);
    $this->data = $data;
  }
  
  public function getType() {
    return "string-content";
  }
}
?>