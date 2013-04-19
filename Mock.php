<?

class Mock {
   use CallSiteStats;

   public function getCallSitePublic() {
      return $this->getCallSite();
   }
}
