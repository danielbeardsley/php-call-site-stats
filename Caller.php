<?
require_once __DIR__ . "/Mock.php";

class Caller {
   public function getCallSite() {
      return $this->m()->getCallSitePublic();
   }

   protected function m() {
      return new Mock();
   }
}

