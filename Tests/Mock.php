<?php

class Mock {
   use CallSiteStats;

   public function getCallSitePublic() {
      return $this->getCallSite();
   }

   public function something() {
      call_user_func_array([$this, 'recordCallSite'], func_get_args());
   }

   protected function isExternalCallSite($file) {
      return $file != __FILE__;
   }
}

