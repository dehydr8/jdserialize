<?php namespace dehydr8\Jdeserialize\utils;

class TypeResolver {

  const INVERSE = array(
    "B" => "byte",
    "C" => "char",
    "D" => "double",
    "F" => "float",
    "I" => "int",
    "J" => "long",
    "S" => "short",
    "Z" => "boolean",
    "L" => "unknown",
  );

  public static function resolve($type) {
    return INVERSE[$type];
  }
}

?>