<?php namespace dehydr8\Jdeserialize\utils;

class Normalizer {

  /**
   * Normalize all of the objects passed to the function
   *
   * @param array $objects
   * @return array
   * @see Normalizer::normalize
   */
  public static function normalizeObjects($objects = array()) {
    $normalized = array();
    foreach ($objects as $object) {
      $o = Normalizer::normalize($object);
      if ($o != null)
        $normalized[] = $o;
    }
    return $normalized;
  }
  
  /**
   * Recursively converts the instance object and creates a representation
   * of that object in an associative array
   *
   * @param Instance $instance
   * @return array normalized instance
   */
  public static function normalize($instance) {
    if (!is_a($instance, 'dehydr8\Jdeserialize\content\Instance'))
      return null;

    $normalized = array();

    if (count($instance->annotations) > 0) {
      foreach ($instance->annotations as $class => $arr) {
        foreach ($arr as $value) {
          if (!is_a($value, 'dehydr8\Jdeserialize\content\BlockData'))
            $normalized[] = Normalizer::value($value);
        }
      }
    } else {
      foreach ($instance->fieldData as $class => $d) {
        foreach ($d as $name => $value) {
          $normalized[$name] = Normalizer::value($value);
        }
      }
    }
    return $normalized;
  }

  private static function value($value) {
    if (is_a($value, 'dehydr8\Jdeserialize\content\Instance')) {
      return Normalizer::normalize($value);
    } else if (is_a($value, 'dehydr8\Jdeserialize\content\StringContent')) {
      return $value->data;
    } else if (is_a($value, 'dehydr8\Jdeserialize\content\ArrayContent')) {
      $values = array();
      foreach ($value->data as $v) {
        $values[] = Normalizer::value($v);
      }
      return $values;
    } else if (is_a($value, 'dehydr8\Jdeserialize\content\EnumContent')) {
      return $value->value;
    }
    return $value;
  }

}

?>