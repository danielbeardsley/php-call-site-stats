<?php

require_once __DIR__ . "/../CallSiteStats.php";
require_once __DIR__ . "/Caller.php";

class CallSiteStatsTest extends PHPUnit_Framework_TestCase {
   public function testCaptureCallSite() {
      $c = $this->c();
      $site = $c->getCallSite();
      $this->assertStringEndsWith('/Caller.php:7', $site);
   }

   public function testRecordCallSite() {
      $m = new Mock();
      $m->something(100);
      $stats = Mock::getCallSiteStats();
      $this->assertStringEndsWith('/CallSiteStatsTest.php:15 100', $stats);

      $m->something('blah');
      $statsMore = explode("\n",$m->getCallSiteStats());
      $this->assertSame($stats, $statsMore[0]);
      $this->assertStringEndsWith('/CallSiteStatsTest.php:19 blah', $statsMore[1]);
   }

   public function testRecordCallSiteStackDepth() {
      $m = new Mock();
      $m->stackSomething(4);
      $stats = Mock::getCallSiteStats();
      $this->assertStringEndsNotWith("end-of-captured-stack 1", $stats);

      $m = new Mock();
      $m->stackSomething(12);
      $stats = Mock::getCallSiteStats();
      $this->assertStringEndsWith('end-of-captured-stack 1', $stats);
   }

   public function testRecordCallSiteArguments() {
      $m = new Mock();
      $m->something('arg1', 'arg2', 'arg3', 'arg4', 'arg5');
      $stats = $m->getCallSiteStats();
      $this->assertStringEndsWith('/CallSiteStatsTest.php:39 arg1 arg2 arg3 arg4 arg5', $stats);
   }

   public function testDisable() {
      $m = new Mock();
      Mock::toggleCallSiteStats(false);
      $m->something(100);
      $this->assertNull($m->getCallSiteStats());
   }

   protected function c() {
      return new Caller();
   }
}

