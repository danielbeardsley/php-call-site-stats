<?

class Mock {
   use CallSiteStats;

   public function getCallSitePublic() {
      return $this->getCallSite();
   }

   protected function isExternalCallSite($file) {
      return $file != __FILE__;
   }
}

