<?php namespace dehydr8\Jdeserialize\content;

abstract class Content {

  private $handle;

  public function __construct($handle) {
    $this->handle = $handle;
  }

  /**
   * the type of content represented by this object.
   *
   */
  public abstract function getType();

  /**
   * Get the numeric handle by which this object was referred to in the object stream.
   * These handles are used internally by Object{Output,Input}Stream as a mechanism to
   * avoid costly duplication.
   *
   * @return int the handle assigned in the stream
   */
  public function getHandle() {
    return $this->handle;
  }
}

?>