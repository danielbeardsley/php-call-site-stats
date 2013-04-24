<?php

/*************************************************************************
 * Allows easy collection of arbitrary line-level stats about
 * function calls into a particular class in a production environment.
 ************************************************************************/
trait CallSiteStats {
   protected static $callSiteStatsEnabled = true;
   protected $_callSiteStats = [];
   public $_callSiteSeconds = 0;

   /**
    * Enable or disable call site stats collection.
    *
    * Intended to allow deploying this library with no execution time cost,
    * and selectively enabling it.
    */
   public static function toggleCallSiteStats($enabled) {
      self::$callSiteStatsEnabled = $enabled;
   }

   /**
    * Returns a multiline string representing all the collected stats
    * (one line per call to `recordCallSite()`) in the format:
    * FILE:LINE ARG1 ARGN...
    *
    * Example:
    *    /path/to/file.php:21 arg1 arg2 ...
    *    /path/to/file.php:21 arg1 arg2 ...
    *    /path/to/other.php:37 arg1 arg2 ...
    *
    * Where each arg passed to recordCallSite() is written out spearated by 
    * spaces.
    *
    * If CallSiteStats collection has been disabled, this returns null.
    */
   public function getCallSiteStats() {
      if (!self::$callSiteStatsEnabled) {
         return null;
      }
      $time = microtime(true);
      $outStats = [];
      foreach($this->_callSiteStats as $site => $stats) {
         foreach($stats as $statLine) {
            $outStats[] = "{$site} {$statLine}";
         }
      }
      $results = implode("\n", $outStats);
      $this->_callSiteSeconds += microtime(true) - $time;
      return $results;
   }

   /**
    * Records the passed arguments for the function 
    * call-site that called into this class.
    */
   protected function recordCallSite() {
      if (!self::$callSiteStatsEnabled) {
         return null;
      }

      $time = microtime(true);
      $callSite = $this->getCallSite();
      if (!$callSite) {
         return;
      }

      $data = implode(' ', func_get_args());
      if (isset($this->_callSiteStats[$callSite])) {
         $this->_callSiteStats[$callSite][] = $data;
      } else {
         $this->_callSiteStats[$callSite] = [$data];
      }
      $this->_callSiteSeconds += microtime(true) - $time;
   }

   /**
    * Returns a string like "path/to/file.php:123" for the first stack frame 
    * down the stack) that is NOT inside this file.
    *
    * Returns null if one can't be found.
    */
   protected function getCallSite() {
      $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);
      $length = count($trace);

      for ($i=0; $i < $length; $i++) {
         $frame = $trace[$i];
         if (isset($frame['file']) &&
          $frame['file'] != __FILE__ &&
          $this->isExternalCallSite($frame['file'])) {
            return $frame['file'] . ":" . $frame['line'];
         }
      }
      return null;
   }

   abstract protected function isExternalCallSite($file);
}
