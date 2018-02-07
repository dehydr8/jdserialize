<?php

require_once(__DIR__ . "/../vendor/autoload.php");

use dehydr8\Jdeserialize\Deserializer;
use dehydr8\Jdeserialize\utils\Normalizer;

$bin = file_get_contents($argv[1]);
$jd = new Deserializer($bin);
$objects = $jd->deserialize();

$normalized = Normalizer::normalizeObjects($objects);
echo json_encode($normalized, JSON_PRETTY_PRINT);

?>