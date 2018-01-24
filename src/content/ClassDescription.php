<?php namespace dehydr8\Jdeserialize\content;

class ClassDescription extends Content {

  public $type;
  public $name;
  public $serialVersionUID;
  public $flags;
  public $fields;
  public $innerClasses;
  public $annotations;
  public $superClass;
  public $interfaces;
  public $enumConstants = array();

  public function __construct($handle, $type) {
    parent::__construct($handle);
    $this->type = $type;
  }

  public function getType() {
    return "class-description-content";
  }

  /**
   * Add an enum constant to the description's set.
   *
   * @param [type] $enum
   * @return void
   */
  public function addEnum($enum) {
    if (!in_array($enum, $this->enumConstants))
      $this->enumConstants[] = $enum;
  }

  /**
   * Generates a list of all class descriptions in this class's hierarchy, in the order
   * described by the Object Stream Serialization Protocol.  This is the order in which
   * fields are read from the stream.
   *
   * @param array $classes an array to be filled in with the hierarchy
   * @return void
   */
  public function getHierarchy($classes = array()) {
    if ($this->superClass != null)
      $classes = $this->superClass->getHierarchy($classes);

    $classes[] = $this;

    return $classes;
  }
}

?>