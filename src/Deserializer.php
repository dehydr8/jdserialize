<?php namespace dehydr8\Jdeserialize;

use dehydr8\Jdeserialize\constants\Constants;
use dehydr8\Jdeserialize\constants\ClassDescriptionType;
use dehydr8\Jdeserialize\content\ClassDescription;
use dehydr8\Jdeserialize\content\Instance;
use dehydr8\Jdeserialize\content\BlockData;
use dehydr8\Jdeserialize\content\StringContent;
use dehydr8\Jdeserialize\content\EnumContent;
use dehydr8\Jdeserialize\content\ArrayContent;

use dehydr8\Jdeserialize\utils\ClassPrinter;
use dehydr8\Jdeserialize\utils\TypeResolver;

/**
 * The deserializer code is heavily inspired (and most bits copied)
 * from https://code.google.com/archive/p/jdeserialize/
 */
class Deserializer {
  private $data;
  private $idx;
  private $handles;

  private $currentHandle;
  private $classDescriptions = array();

  private $logger;
  
  public function __construct($data, $logger = null) {
    $this->data = $data;
    $this->idx = 0;
    $this->handles = array();
    $this->currentHandle = Constants::BASE_WIRE_HANDLE;
    $this->logger = $logger;
  }

  private function newHandle() {
    return $this->currentHandle++;
  }

  private function saveHandle($handle, $content) {
    $this->handles[$handle] = $content;
  }

  private function unpack($format, $increment = 0) {
    $ret = unpack($format, substr($this->data, $this->idx));
    $this->idx += $increment;
    return $ret[1];
  }

  /**
   * Converts a 8 byte hexstring to a long
   * 
   * https://stackoverflow.com/questions/34067202/convert-64-bit-hexadecimal-to-float-in-php
   */
  private function hex2float($strHex) {
    $hex = sscanf($strHex, "%02x%02x%02x%02x%02x%02x%02x%02x");
    $hex = array_reverse($hex);
    $bin = implode('', array_map('chr', $hex));
    $array = unpack("dnum", $bin);
    return $array['num'];
  }

  /**
   * read the long as a hex-string
   */
  private function readLongHex() {
    $length = 8;
    $value = substr($this->data, $this->idx, $length);
    $this->idx += $length;
    return bin2hex($value);
  }

  /**
   * Reads the next signed long (8 byte)
   * FIX: might not return signed longs correctly
   * 
   * http://www.docjar.com/html/api/java/io/DataInputStream.java.html
   */
  private function readLong() {
    return
      ($this->readByte() << 56) +
      ($this->readByte() & 255 << 48) +
      ($this->readByte() & 255 << 40) +
      ($this->readByte() & 255 << 32) +
      ($this->readByte() & 255 << 24) +
      ($this->readByte() & 255 << 16) +
      ($this->readByte() & 255 << 8) +
      ($this->readByte() & 255 << 0);
  }

  /**
   * Reads the next signed integer (4 byte)
   * 
   * @return int
   */
  private function readInt() {
    $unsigned = $this->unpack("N", 4);
    return unpack("l", pack("l", $unsigned))[1];
  }

  private function readShort() {
    return
      ($this->readByte() << 8) +
      ($this->readByte() << 0);
  }

  private function readByte() {
    return $this->unpack("C", 1);
  }

  private function readChar() {
    return chr($this->readShort());
  }

  private function readDouble() {
    return $this->hex2float($this->readLongHex());
  }

  private function readFloat() {
    return $this->unpack("G", 4);
  }

  private function readBoolean() {
    return $this->readByte() != 0;
  }

  private function readString() {
    $length = $this->readShort();
    $value = substr($this->data, $this->idx, $length);
    $this->idx += $length;
    return $value;
  }

  private function readPrevObject() {
    $handle = $this->readInt();
    $this->log("reading reference for handle " . dechex($handle));
    if (!isset($this->handles[$handle])) {
      throw new \Exception("Failure finding an entry for handle: " . dechex($handle));
    }
    $prev = $this->handles[$handle];
    return $prev;
  }

  private function readClassAnnotation() {
    $this->log("reading class annotations");
    $annotations = array();
    while (true) {
      $tc = $this->readByte();
      if ($tc === Constants::TC_ENDBLOCKDATA) {
        return $annotations;
      }
      if ($tc === Constants::TC_RESET) {
        //reset();
        continue;
      }
      $c = $this->readContent($tc, true);
      $annotations[] = $c;
    }
  }
  
  private function readNewString($tc) {
    if ($tc === Constants::TC_REFERENCE) {
      return $this->readPrevObject();
    }

    $handle = $this->newHandle();
    $length = 0;

    if ($tc === Constants::TC_STRING) {
      $length = $this->readShort();
    } else if ($tc === Constants::TC_LONGSTRING) {
      throw new \Exception("readNewString TC_LONGSTRING not implemented");
    } else if ($tc === Constants::TC_NULL) {
      throw new \Exception("stream signaled TC_NULL when string type expected!");
    } else {
      throw new \Exception("invalid tc byte in string: " . dechex(tc));
    }

    $content = substr($this->data, $this->idx, $length);
    $this->idx += $length;

    $stringContent = new StringContent($handle, $content);
    $this->saveHandle($handle, $stringContent);

    return $stringContent;
  }

  private function handleClassDesc($tc, $mustBeNew = true) {
    if ($tc === Constants::TC_CLASSDESC) {
      $this->log("reading new class description");
      
      $name = $this->readString();
      $serialVersionUID = $this->readLongHex();
      $handle = $this->newHandle();
      $flags = $this->readByte();
      $numberOfFields = $this->readShort();

      if ($numberOfFields < 0)
        throw new \Exception("Invalid number of fields: " . $numberOfFields);
      
      $fields = array();

      for ($i=0; $i<$numberOfFields; $i++) {
        $ftype = chr($this->readByte());
        if (in_array($ftype, array('B', 'C', 'D', 'F', 'I', 'J', 'S', 'Z'))) {
          $fname = $this->readString();
          $fields[] = array(
            "name" => $fname,
            "type" => $ftype
          );
        } else if (in_array($ftype, array('[', 'L'))) {
          $fname = $this->readString();
          $stc = $this->readByte();
          $className = $this->readNewString($stc);
          $fields[] = array(
            "name" => $fname,
            "type" => $ftype,
            "className" => $className
          );
        } else {
          throw new \Exception("invalid field type char: " . dechex($ftype));
        }
      }

      $classdesc = new ClassDescription($handle, ClassDescriptionType::NORMALCLASS);
      $classdesc->name = $name;
      $classdesc->serialVersionUID = $serialVersionUID;
      $classdesc->flags = $flags;
      $classdesc->fields = $fields;
      $classdesc->annotations = $this->readClassAnnotation();
      $classdesc->superClass = $this->readClassDesc();

      $this->saveHandle($handle, $classdesc);
      $this->classDescriptions[] = $classdesc;
      return $classdesc;

    } else if ($tc === Constants::TC_NULL) {
      if ($mustBeNew) {
        throw new \Exception("Expected new class description, got null!");
      }
      return null;

    } else if ($tc === Constants::TC_REFERENCE) {
      $this->log("got a reference for the class description, reading...");

      if ($mustBeNew) {
        throw new \Exception("Expected new class description, got a reference!");
      }
      $classdesc = $this->readPrevObject();
      return $classdesc;

    } else if ($tc === Constants::TC_PROXYCLASSDESC) {
      $handle = $this->newHandle();
      $icount = $this->readInt();

      if ($icount < 0) {
        throw new \Exception("Invalid proxy interface count: " . dechex($icount));
      }

      $interfaces = array();
      for ($i=0; $i<$icount; $i++)
        $interfaces[] = $this->readString();

      $classdesc = new ClassDescription($handle, ClassDescriptionType::PROXYCLASS);
      $classdesc->name = "(proxy class; no name)";
      $classdesc->interfaces = $interfaces;
      $classdesc->superClass = $this->readClassDesc();
      
      $this->saveHandle($handle, $classdesc);
      return $classdesc;

    } else {
      throw new \Exception("Expected a valid class description starter, got: " . dechex($tc));
    }
  }

  private function readClassDesc() {
    $tc = $this->readByte();
    return $this->handleClassDesc($tc, false);
  }

  private function readFieldValue($type) {
    $this->log("reading field value for type: " . $type);
    switch ($type) {
      // byte
      case 'B': return $this->readByte();
      // char
      case 'C': return $this->readChar();
      // double
      case 'D': return $this->readDouble();
      // float
      case 'F': return $this->readFloat();
      // int
      case 'I': return $this->readInt();
      // long
      case 'J': return $this->readLong();
      // short
      case 'S': return $this->readShort();
      // boolean
      case 'Z': return $this->readBoolean();
      // array, object
      case '[':
      case 'L': {
        $stc = $this->readByte();
        return $this->readContent($stc, false);
      }
      default: throw new \Exception("readFieldValue: Cannot process type: $type");
    }
  }

  private function readClassData($instance) {
    $this->log("reading classdata for " . $instance->classDescription->name . " (" . $instance->getHandle() . ")");
    foreach ($instance->classDescription->getHierarchy() as $class) {
      if (($class->flags & Constants::SC_SERIALIZABLE) != 0) {

        foreach ($class->fields as $field) {
          $this->log("will read field of type <" . $field["type"] . "> with name '" . $field["name"] . "'");
          $value = $this->readFieldValue($field["type"]);
          $instance->fieldData[$class->name][$field["name"]] = $value;

          $this->log("read field '" . $field["name"] . "' of class " . $class->name);
        }
        if (($class->flags & Constants::SC_WRITE_METHOD) != 0) {
          if (($class->flags & Constants::SC_ENUM) != 0) {
            throw new \Exception("SC_ENUM & SC_WRITE_METHOD encountered!");
          }

          $instance->annotations[$class->name] = $this->readClassAnnotation();
        }
      } else if(($class->flags & Constants::SC_EXTERNALIZABLE) != 0) {
        if (($class->flags & Constants::SC_BLOCK_DATA) != 0) {
          throw new \Exception("hit externalizable with nonzero SC_BLOCK_DATA; can't interpret data");
        } else {
          $instance->annotations[$class->name] = $this->readClassAnnotation();
        }
      }
    }
  }

  private function readNewObject() {
    $this->log("reading new object");
    $description = $this->readClassDesc();
    $handle = $this->newHandle();

    $instance = new Instance($handle);
    $instance->classDescription = $description;

    $this->saveHandle($handle, $instance);
    $this->readClassData($instance);

    return $instance;
  }

  private function readBlockData($tc) {
    $this->log("reading block data, tc => " . dechex($tc));
    $size = -1;
    if ($tc == Constants::TC_BLOCKDATA) {
      $size = $this->readByte();
    } else if ($tc == Constants::TC_BLOCKDATALONG) {
      $size = $this->readInt();
    }

    if ($size < 0)
      throw new \Exception("Invalid value for blockdata size: $size");

    $value = substr($this->data, $this->idx, $size);
    $this->idx += $size;
    return new BlockData(null, $value);
  }

  private function readArrayValues($type) {
    $size = $this->readInt();
    $values = array();
    for ($i=0; $i<$size; $i++) {
      $values[] = $this->readFieldValue($type);
    }
    return $values;
  }

  private function readNewArray() {
    $cd = $this->readClassDesc();
    $handle = $this->newHandle();
    $this->log("reading new array: handle " . dechex($handle) . ", class: " . $cd->name);
    $values = $this->readArrayValues(substr($cd->name, 1, 1));
    $ac = new ArrayContent($handle, $cd->name, $values);
    $this->saveHandle($handle, $ac);
    return $ac;
  }

  private function readNewClass() {
    $tc = $this->readByte();
    return $this->handleClassDesc($tc, true);
  }

  private function readNewEnum() {
    $cd = $this->readClassDesc();
    $handle = $this->newHandle();
    $this->log("reading new enum: handle " . dechex($handle) . ", class: " . $cd->name);

    $tc = $this->readByte();
    $so = $this->readNewString($tc);
    
    $this->saveHandle($handle, $so);

    $cd->addEnum($so->data);
    return new EnumContent($handle, $cd, $so->data);
  }

  private function readContent($tc, $blockData = true) {
    $this->log("reading content, tc => " . dechex($tc) . " (offset " . $this->idx . ")");
    switch ($tc) {
      case Constants::TC_NULL:
        return null;
      case Constants::TC_CLASS:
        return $this->readNewClass();
      case Constants::TC_OBJECT:
        return $this->readNewObject();
      case Constants::TC_ARRAY:
        return $this->readNewArray();
      case Constants::TC_ENUM:
        return $this->readNewEnum();
      case Constants::TC_STRING:
      case Constants::TC_LONGSTRING:
        return $this->readNewString($tc);
      case Constants::TC_REFERENCE:
        return $this->readPrevObject();
      case Constants::TC_BLOCKDATA:
      case Constants::TC_BLOCKDATALONG:
        return $this->readBlockData($tc);
      default:
        throw new \Exception("Unknown content tc byte in stream: " . dechex($tc));
    }
  }

  private function log($message) {
    if ($this->logger != null) {
      $this->logger->log($message);
    }
  }

  private function hasMoreContent() {
    return $this->idx < strlen($this->data);
  }

  private static function decodeClassName($name, $convertSlashes = true) {
    $name = substr($name, 1, strlen($name) - 2);
    if ($convertSlashes)
      $name = str_replace("/", ".", $name);
    return $name;
  }

  public static function resolveJavaType($type, $className) {
    if ($type == "L") {
      return self::decodeClassName($className);
    } else if ($type == "[") {
      $suffix = "";
      for ($i=0; $i<strlen($className); $i++) {
        $ch = $className[$i];
        switch ($ch) {
          case '[':
            $suffix .= "[]";
            continue;
          case 'L':
            return self::decodeClassName(substr($className, $i)) . $suffix;
          default:
            return TypeResolver::INVERSE[$ch] . $suffix;
        }
      }

      return $className;
    }
    return TypeResolver::INVERSE[$type];
  }

  public function printClasses() {
    foreach ($this->classDescriptions as $description) {
      ClassPrinter::display($description);
    }
  }

  public function getClasses() {
    return $this->classDescriptions;
  }

  public function deserialize() {
    $magic = $this->readShort();

    if ($magic !== Constants::STREAM_MAGIC)
      throw new \Exception("Magic mismatch! expected " . Constants::STREAM_MAGIC . ", got " . $magic);

    $version = $this->readShort();

    if ($version !== Constants::STREAM_VERSION)
      throw new \Exception("Version mismatch! expected " . Constants::STREAM_VERSION . ", got " . $version);

    $objects = array();

    while (true && $this->hasMoreContent()) {
      $tc = $this->readByte();
      $content = $this->readContent($tc, true);

      $this->log("successfully read content of type '" . $content->getType() . "'");
      $objects[] = $content;
    }

    return $objects;
  }
}

?>