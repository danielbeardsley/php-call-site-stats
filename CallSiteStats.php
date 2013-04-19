<?

/*************************************************************************
 * Allows easy collection of arbitrary line-level stats about
 * function calls into a particular class in a production environment.
 ************************************************************************/
trait CallSiteStats {
   protected $_callSiteStats = [];
   public $_callSiteSeconds = 0;

   public function getCallSiteStats() {
      $time = microtime(true);
      $outStats = [];
      foreach($this->_callSiteStats as $site => $stats) {
         $outStats[] = "{$site} {$stats[0]} {$stats[1]}";
      }
      $results = implode("\n", $outStats);
      $this->_callSiteSeconds += microtime(true) - $time;
      return $results;
   }

   /**
    * Records a get / hit count for the function call-site that
    * called into this class.
    */
   protected function recordCallSite($getCount, $hitCount) {
      $time = microtime(true);
      $callSite = $this->getCallSite();
      if (!$callSite) {
         return;
      }

      if (isset($this->_callSiteStats[$callSite])) {
         $currentStats = &$this->_callSiteStats[$callSite];
         $currentStats[0] += $getCount;
         $currentStats[1] += $hitCount;
      } else {
         $this->_callSiteStats[$callSite] = [$getCount, $hitCount];
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
         if (isset($frame['file']) && $this->isExternalCallSite($frame['file'])) {
            return $frame['file'] . ":" . $frame['line'];
         }
      }
      return null;
   }

   protected function isExternalCallSite($file) {
      return $file != __FILE__;
   }
}
