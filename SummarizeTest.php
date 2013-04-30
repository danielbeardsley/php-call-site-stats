<?php

class SummarizeTest extends PHPUnit_Framework_TestCase {
   public function testCaptureCallSite() {
      $this->assertCommandSuccessful(<<<EOT
blah.php:23 3 7
blah.php:23 2 3
EOT
   ,'--ratio=1,2', 'blah.php:23 5/10 = 50%');
   }

   private function assertCommandSuccessful(
    $input, $arguments, $expectedOutput) {
      list($exitcode, $output) = $this->exec($input, $arguments);
      $this->assertSame(0, $exitcode, "Process existed with non-zero status");
      $this->assertSame($expectedOutput, $output, "Process existed with non-zero status");
   }

   /**
    * Runs `summarize.php` passes the given input as a file argument,
    * along with the specified commands and returns an array of:
    * [exitcode, outputstr]
    */
   private function exec($input, $arguments) {
      $tempfile = tempnam(sys_get_temp_dir(), "test-input"); 
      file_put_contents($tempfile, $input);
      $tempfile = escapeshellarg($tempfile);

      $command = "./summarize.php -f $tempfile $arguments 2>&1";
      exec($command, $output, $exitcode);

      return [$exitcode, implode("\n", $output)];
   }
}

