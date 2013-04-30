#!/usr/bin/php
<?

$filename = getopt('f:')['f'];
$stats = new StatsCollection(fopen($filename,'r'));
$stats->printRatios();

class StatsCollection {
   public function __construct($inStream) {
      while ($line = fgets($inStream)) {
         $this->addLine($line);
      }
      fclose($inStream);
   }

   protected function addLine($line) {
      $parts = explode(" ", $line);
      $this->lines[$parts[0]][] = array_slice($parts, 1);
   }

   public function printRatios() {
      $numerator = 0;
      $denominator = 1;
      $firstLine = reset($this->lines);
      $blankSumRow = array_fill(0,count($firstLine[0]),0);
      foreach($this->lines as $key => $rows) {
         $sums = $blankSumRow;
         foreach($rows as $rowIndex => $row) {
            foreach($row as $i => $value) {
               $sums[$i] += $value;
            }
         }
         $ratio = round(100 * ($sums[$numerator] / $sums[$denominator]), 2);
         echo "$key {$sums[$numerator]} / {$sums[$denominator]} = $ratio%\n";
      }
   }
}

