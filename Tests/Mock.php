<?php

class Mock {
   use CallSiteStats;

   public static function getCallSitePublic() {
      return self::getCallSite();
   }

   public function something() {
      call_user_func_array([$this, 'recordCallSite'], func_get_args());
   }

   protected static function isExternalCallSite($file) {
      return $file != __FILE__;
   }
}

