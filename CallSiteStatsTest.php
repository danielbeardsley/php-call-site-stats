<?

require_once __DIR__ . "/CallSiteStats.php";
require_once __DIR__ . "/Caller.php";

class CallSiteStatsTest extends PHPUnit_Framework_TestCase {
   public function testCaptureCallSite() {
      $c = $this->c();
      $site = $c->getCallSite();
      $this->assertStringEndsWith('/Caller.php:6', $site);
   }

   protected function c() {
      return new Caller();
   }
}

