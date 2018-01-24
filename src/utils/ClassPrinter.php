<?php namespace dehydr8\Jdeserialize\utils;

use dehydr8\Jdeserialize\constants\ClassDescriptionType;
use dehydr8\Jdeserialize\constants\Constants;
use dehydr8\Jdeserialize\Deserializer;

class ClassPrinter {

  /**
   * prints the class representation on stdout
   *
   * @param ClassDescription $description
   * @return void
   */
  public static function print($description) {
    if ($description->type === ClassDescriptionType::NORMALCLASS) {
      echo "// " . dechex($description->getHandle()) . "\r\n";
      if (($description->flags & Constants::SC_ENUM) != 0) {
        echo "enum " . $description->name . " {\r\n";
        echo "    " . implode(", ", $description->enumConstants) . "\r\n";
        echo "}\r\n\r\n";
      } else {
        echo "class " . ($description->name[0] === "[" ? Deserializer::resolveJavaType("[", $description->name) : $description->name);
        if ($description->superClass != null) {
          echo " extends " . $description->superClass->name;
        }
        echo " implements ";
        if (($description->flags & Constants::SC_EXTERNALIZABLE) != 0) {
          echo "java.io.Externalizable";
        } else {
          echo "java.io.Serializable";
        }
        echo " {\r\n";
        echo "    private static final long serialVersionUID = 0x" . $description->serialVersionUID . "L;\r\n\r\n";
        foreach ($description->fields as $field) {
          echo "    " . Deserializer::resolveJavaType($field["type"], @$field["className"]->data) . " " . $field["name"] . ";\r\n";
        }
        echo "}\r\n\r\n";
      }
    }
  }
}

?>