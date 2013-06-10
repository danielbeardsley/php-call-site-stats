<?php

class SummarizeTest extends PHPUnit_Framework_TestCase {
   public function testRatios() {
      $this->assertCommandSuccessful(<<<EOT
blah.php:23 blah 3 7
blah.php:23 blah 2 3
EOT
      ,'--ratio=2:3', 'blah.php:23 diff:5 5 / 10 = 50%');
      $this->assertCommandSuccessful(<<<EOT
other.php:9 9 10
blah.php:23 2 3
other.php:9 100 101
blah.php:23 2 3
EOT
      ,'--ratio=1:2', <<<EOT
other.php:9 diff:2 109 / 111 = 98.2%
blah.php:23 diff:2 4 / 6 = 66.67%
EOT
);
   }

   public function testStats() {
      $this->assertCommandSuccessful(<<<EOT
blah.php:23 blah 9
blah.php:23 blah 4
blah.php:23 blah 3
EOT
      ,'--stats=2', 'blah.php:23 avg:5.333 count:3 sum:16 std:2.625 min:3 max:9');
   }

   public function testSTDIN() {
      $this->assertSTDINCommandSuccessful(<<<EOT
echo "blah.php:23 blah 9
blah.php:23 blah 4
blah.php:23 blah 3"
EOT
      ,'--stats=2', 'blah.php:23 avg:5.333 count:3 sum:16 std:2.625 min:3 max:9');
   }

   private function assertCommandSuccessful(
    $input, $arguments, $expectedOutput) {
      list($exitcode, $output) = $this->exec($input, $arguments);
      $this->assertSame($expectedOutput, $output);
      $this->assertSame(0, $exitcode, "Process existed with non-zero status");
   }

   private function assertSTDINCommandSuccessful(
    $input, $arguments, $expectedOutput) {
      $command = "$input | php " . __DIR__ . "/../summarize.php " . 
       "$arguments 2>&1";
      exec($command, $output, $exitcode);
      $output = implode("\n", $output);
      $this->assertSame($expectedOutput, $output);
      $this->assertSame(0, $exitcode, "Process existed with non-zero status");
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

      $command = "php " . __DIR__ . "/../summarize.php " . 
       "-f $tempfile $arguments 2>&1";
      exec($command, $output, $exitcode);

      return [$exitcode, implode("\n", $output)];
   }
}

