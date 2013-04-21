<?

require_once __DIR__ . "/CallSiteStats.php";
require_once __DIR__ . "/Caller.php";

class CallSiteStatsTest extends PHPUnit_Framework_TestCase {
   public function testCaptureCallSite() {
      $c = $this->c();
      $site = $c->getCallSite();
      $this->assertStringEndsWith('/Caller.php:6', $site);
   }

   public function testRecordCallSite() {
      $m = new Mock();
      $m->something(100);
      $stats = $m->getCallSiteStats();
      $this->assertStringEndsWith('/CallSiteStatsTest.php:15 100 0', $stats);
   }

   protected function c() {
      return new Caller();
   }
}

