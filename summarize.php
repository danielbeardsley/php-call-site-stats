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
         foreach($rows as $rowIndex => $row) {
            $value = floatval($row[$column]); 
            $sum += $value;
            if ($min === null) {
               $min = $max = $value;
            }
            $min = min($min, $value);
            $max = max($max, $value);
         }
         $avg = $sum / count($rows);
         echo "$key min:$min max:$max avg:$avg";
      }
   }
}

