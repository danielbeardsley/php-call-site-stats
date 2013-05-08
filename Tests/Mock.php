<?php

class Mock {
   use CallSiteStats;

   public function __construct() {
      self::clearStats();
   }

   public static function getCallSitePublic() {
      return self::getCallSite();
   }

   public static function clearStats() {
      self::$_callSiteStats = [];
   }

   public function something() {
      call_user_func_array([$this, 'recordCallSite'], func_get_args());
   }

   public function stackSomething($depth) {
      if ($depth <= 0) {
         $this->something();
      } else {
         $this->stackSomething($depth - 1);
      }
   }

   protected static function isExternalCallSite($file) {
      return $file != __FILE__;
   }
}

