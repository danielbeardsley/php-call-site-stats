#!/usr/bin/php
<?

$options = getopt('f:',['ratio:','stats:']);
$filename = $options['f'];
$stats = new StatsCollection(fopen($filename,'r'));
if ($options['stats']) {
   $stats->printStats();
} else if ($options['ratio']) {
   $stats->printRatios();
}


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

   public function printStats() {
      $column = 0;
      foreach($this->lines as $key => $rows) {
         $sum = 0;
         $min = null;
         $max = null;
         $values = [];
         foreach($rows as $rowIndex => $row) {
            $values[] = floatval($row[$column]); 
         }
         $min = min($values);
         $max = max($values);
         $sum = array_sum($values);
         $avg = $sum / count($values);
         $sumOfSquares = 0;
         foreach($values as $value) {
            $sumOfSquares += $value * $value;
         }
         $std = sqrt(($sumOfSquares / count($values)) - $avg*$avg);
         $std = round($std, 3);
         $avg = round($avg, 3);
         echo "$key min:$min max:$max avg:$avg std:$std";
      }
   }
}

