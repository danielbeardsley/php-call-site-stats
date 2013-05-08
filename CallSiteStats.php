<?php

/*************************************************************************
 * Allows easy collection of arbitrary line-level stats about
 * function calls into a particular class in a production environment.
 ************************************************************************/
trait CallSiteStats {
   protected static $callSiteStatsEnabled = true;
   protected static $_callSiteStats = [];

   /**
    * The number of stack frames below the current one to capture (more == 
    * slower, higher == greater risk of missingsome stats)
    */
   protected static $stackCaptureDepth = 10;

   public static $_callSiteSeconds = 0;

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
    * Note: This format is designed to be post-processed with summarize.php
    *
    * If CallSiteStats collection has been disabled, this returns null.
    */
   public static function getCallSiteStats() {
      if (!self::$callSiteStatsEnabled) {
         return null;
      }
      $time = microtime(true);
      $outStats = [];
      foreach(self::$_callSiteStats as $site => $stats) {
         foreach($stats as $statLine) {
            $outStats[] = "{$site} {$statLine}";
         }
      }
      $results = implode("\n", $outStats);
      self::$_callSiteSeconds += microtime(true) - $time;
      return $results;
   }

   /**
    * Records the passed arguments for the function 
    * call-site that called into this class.
    */
   protected static function recordCallSite() {
      if (!self::$callSiteStatsEnabled) {
         return null;
      }

      $time = microtime(true);
      $callSite = self::getCallSite();
      $data = implode(' ', func_get_args());

      // If we don't have a call-site it's because we reached the end of our 
      // partial stack before isExternalCallSite() returned true, meaning all 
      // the frames we captured were inside the class we were trying to 
      // measure.
      if (!$callSite) {
         $callSite = "end-of-captured-stack";
         $data = "1";
      }

      if (isset(self::$_callSiteStats[$callSite])) {
         self::$_callSiteStats[$callSite][] = $data;
      } else {
         self::$_callSiteStats[$callSite] = [$data];
      }
      self::$_callSiteSeconds += microtime(true) - $time;
   }

   /**
    * Returns a string like "path/to/file.php:123" for the first stack frame 
    * down the stack that is NOT inside this file.
    *
    * Returns null if one can't be found.
    */
   protected static function getCallSite() {
      $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,
       static::$stackCaptureDepth);
      $length = count($trace);

      for ($i=0; $i < $length; $i++) {
         $frame = $trace[$i];
         if (isset($frame['file']) &&
          $frame['file'] != __FILE__ &&
          self::isExternalCallSite($frame['file'])) {
            return $frame['file'] . ":" . $frame['line'];
         }
      }
      return null;
   }

   abstract protected function isExternalCallSite($file);
}
