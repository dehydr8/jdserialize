<?php

use dehydr8\Jdeserialize\Deserializer;
use dehydr8\Jdeserialize\utils\Normalizer;
use dehydr8\Jdeserialize\utils\ClassPrinter;

class BorrowedTest extends PHPUnit_Framework_TestCase {

  public function testJCEKSIssue5() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/jceks_issue_5.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);
    $poi = $normalized[0];

    $this->assertTrue($poi["paramsAlg"] == "PBEWithMD5AndTripleDES");
    $this->assertTrue($poi["sealAlg"] == "PBEWithMD5AndTripleDES");
  }

  public function testObj0() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj0.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 0);
  }

  public function testObj1() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj1.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 0);
  }

  public function testObj2() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj2.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 0);
  }

  public function testObj3() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj3.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 0);
  }

  public function testObj4() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj4.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 0);
  }

  public function testObj5() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj5.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 0);
  }

  public function testObj6() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj6.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 0);
  }

  public function testObj7() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/obj7.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);

    $this->assertTrue(count($normalized) == 1);
  }

  public function testObjArrays() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/objArrays.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);
    $poi = $normalized[0];

    $this->assertTrue($poi["boolArr"] === array(true, false, true));
    $this->assertTrue(count($poi["concreteArr"]) == 2);
    $this->assertTrue($poi["integerArr"] === array(1, 2, 3));
    $this->assertTrue($poi["stringArr"] === array("1", "2", "3"));
  }

  public function testObjCollections() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/objCollections.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);
    $poi = $normalized[0];

    $this->assertTrue($poi["arrayList"] === array("e1", "e2"));
    $this->assertTrue($poi["linkedList"] === array("ll1", "ll2"));
  }

  public function testObjEnums() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/objEnums.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);
    $poi = $normalized[0];

    $this->assertTrue($poi["color"] === "GREEN");
    $this->assertTrue($poi["colors"] === array("GREEN", "BLUE", "RED"));
  }

  public function testObjSuper() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/objSuper.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);
    $poi = $normalized[0];

    $this->assertTrue($poi["bool"] === true);
    $this->assertTrue($poi["integer"] === -1);
    $this->assertTrue($poi["superString"] === "Super!!");
    $this->assertTrue($poi["childString"] === "Child!!");
  }

  public function testSunExample() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/sunExample.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);
    
    $this->assertTrue(count($normalized) == 2);
    $this->assertTrue($normalized[0]["value"] === 17);
    $this->assertTrue($normalized[0]["next"]["value"] === 19);
    $this->assertTrue($normalized[1]["value"] === 19);
  }

  public function testClassWithByteArray() {
    $jd = new Deserializer(file_get_contents(__DIR__ . "/../samples/borrowed/testClassWithByteArray.ser"));
    $objects = $jd->deserialize();
    $normalized = Normalizer::normalizeObjects($objects);
    $poi = $normalized[0];
    
    $this->assertTrue($poi["myArray"] === array(1, 3, 7, 11));
  }
  
}
?>