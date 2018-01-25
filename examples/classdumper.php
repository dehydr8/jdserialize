<?php

require_once(__DIR__ . "/../vendor/autoload.php");

use dehydr8\Jdeserialize\Deserializer;
use dehydr8\Jdeserialize\utils\ClassPrinter;

$bin = file_get_contents(__DIR__ . "/../samples/people.bin");
$jd = new Deserializer($bin);
$objects = $jd->deserialize();

foreach ($jd->getClasses() as $description) {
  ClassPrinter::display($description);
}