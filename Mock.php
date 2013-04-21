<?

class Mock {
   use CallSiteStats;

   public function getCallSitePublic() {
      return $this->getCallSite();
   }

   public function something($x) {
      $this->recordCallSite($x, 0);
   }

   protected function isExternalCallSite($file) {
      return $file != __FILE__;
   }
}

