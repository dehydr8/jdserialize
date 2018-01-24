<?php 

/**
 * The tests will be conducted against this dump
 * 
 * 00000000 AC ED 00 05 73 72 00 2A 63 6F 6D 2E 63 6F 64 69 ....sr.*com.codi
 * 00000010 6E 67 68 61 7A 61 72 64 2E 73 65 72 69 61 6C 69 nghazard.seriali
 * 00000020 7A 61 74 69 6F 6E 2E 6D 6F 64 65 6C 2E 42 61 73 zation.model.Bas
 * 00000030 69 63 AE 1F 6B E7 50 B8 A3 7F 02 00 08 5A 00 09 ic..k.P......Z..
 * 00000040 62 5F 62 6F 6F 6C 65 61 6E 42 00 06 66 5F 62 79 b_booleanB..f_by
 * 00000050 74 65 43 00 06 66 5F 63 68 61 72 44 00 08 66 5F teC..f_charD..f_
 * 00000060 64 6F 75 62 6C 65 46 00 07 66 5F 66 6C 6F 61 74 doubleF..f_float
 * 00000070 49 00 05 66 5F 69 6E 74 4A 00 06 66 5F 6C 6F 6E I..f_intJ..f_lon
 * 00000080 67 53 00 07 73 5F 73 68 6F 72 74 78 70 01 01 00 gS..s_shortxp...
 * 00000090 61 3F F1 99 99 99 99 99 9A 40 0C CC CD 00 00 00 a?.......@......
 * 000000A0 03 00 00 00 00 00 00 00 04 00 05                ...........
 * 
 * {
 *   "f_byte": 1,
 *   "f_char": "a",
 *   "f_double": 1.1,
 *   "f_float": 2.2,
 *   "f_int": 3,
 *   "f_long": 4,
 *   "s_short": 5,
 *   "b_boolean": true
 * } 
 */
class PrimitiveDatatypeTest extends PHPUnit_Framework_TestCase {

  protected static $normalized;
  
  public static function setUpBeforeClass() {
    $bin = file_get_contents(__DIR__ . "/../samples/primitive_types.bin");
    $jd = new dehydr8\Jdeserialize\Deserializer($bin);
    $objects = $jd->deserialize();

    // normalize the first object
    self::$normalized = dehydr8\Jdeserialize\utils\Normalizer::normalize($objects[0]);
  }
  
  public static function tearDownAfterClass() {
    self::$normalized = null;
  }

  public function testIfBooleanIsDeserializedCorrectly() {
    $this->assertTrue(self::$normalized["b_boolean"] === true);
  }

  public function testIfShortIsDeserializedCorrectly() {
    $this->assertTrue(self::$normalized["s_short"] === 5);
  }

  public function testIfLongIsDeserializedCorrectly() {
    $this->assertTrue(self::$normalized["f_long"] === 4);
  }

  public function testIfIntIsDeserializedCorrectly() {
    $this->assertTrue(self::$normalized["f_int"] === 3);
  }

  public function testIfDoubleIsDeserializedCorrectly() {
    $this->assertTrue(self::$normalized["f_double"] === 1.1);
  }

  public function testIfCharIsDeserializedCorrectly() {
    $this->assertTrue(self::$normalized["f_char"] === 'a');
  }

  public function testIfByteIsDeserializedCorrectly() {
    $this->assertTrue(self::$normalized["f_byte"] === 0x01);
  }

  /**
   * Clarification
   * 
   * The rounding is done intentionally as the float is calculated to
   * 2.2000000476837
   * 
   * $packed = pack("G", 2.2);
   * $unpacked = unpack("G", $packed);
   * $unpacked[1] would contain 2.2000000476837
   * 
   * Java and PHP both use IEEE 754 format for storing floats
   * 
   * @return void
   */
  public function testIfFloatIsDeserializedCorrectly() {
    $this->assertTrue(round(self::$normalized["f_float"], 2) === 2.2);
  }
}

?>