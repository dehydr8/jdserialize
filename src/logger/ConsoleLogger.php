<?php namespace dehydr8\Jdeserialize\logger;

class ConsoleLogger extends Logger {
  public function log($message) {
    echo "[-] $message\r\n";
  }
}
?>