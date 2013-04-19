<?

require_once __DIR__ . "/CallSiteStats.php";
require_once __DIR__ . "/Mock.php";

class CallSiteStatsTest extends PHPUnit_Framework_TestCase {
   public function testCaptureCallSite() {
      $m = $this->m();
      $site = $m->getCallSitePublic();
      $this->assertStringEndsWith('/Mock.php:7', $site);
   }

   protected function m() {
      return new Mock();
   }
}

