<?php namespace dehydr8\Jdeserialize\logger;
  
abstract class Logger {
  /**
   * Log the message as desired
   *
   * @param string $message
   * @return void
   */
  public abstract function log($message);
}

?>