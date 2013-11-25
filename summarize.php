<?php

$options = getopt('f:',['ratio:','stats:']);
$filename = array_key_exists('f', $options) ? $options['f'] : "php://stdin";

if (isset($options['stats'])) {
   $column = $options['stats'];
   $stats = new StatsCollection('stats', intval($column));
} else if (isset($options['ratio'])) {
   $columns = explode(':',$options['ratio']);
   $stats = new StatsCollection('ratio',
                                intval($columns[0]),
                                intval($columns[1]));
} else {
   echo <<<HELP
Aggregate call-site stats recorded by CallSiteStats.php input columns
are expected to be space-delimited. Lines are grouped by column 1.

Usage:
   summarize --stats=2 -f stats-input.log
   summarize --ratio=3:4 -f stats-input.log
   cat stats-input.log | summarize --stats=2

Options:
   -f=filename    Input filename (if not specified, STDIN is used)
   --stats=x      Calculate min/max/mean/std .. for a given column x
   --ratio=x:y    Total and calculate ratio between column x and column y
HELP;
   exit(1);
}

$stats->readLines(fopen($filename,'r'));
$stats->printResults();


class StatsCollection {
   public function __construct($type, $column1, $column2 = null) {
      $this->stats = $type == 'stats';
      $this->ratio = $type == 'ratio';
      $this->column1 = $column1;
      if ($this->ratio) {
         $this->column2 = $column2;
      }
   }


   public function readLines($inStream) {
      while ($line = fgets($inStream)) {
         $this->addLine($line);
      }
      fclose($inStream);
   }

   protected function addLine($line) {
      $parts = explode(" ", $line);
      $lineData = &$this->lines[$parts[0]];
      if ($this->stats) {
         if ($lineData === null){ 
            $lineData = new StatsStream();
         }
         $lineData->write($parts[$this->column1]);
      } else if ($this->ratio) {
         if ($lineData === null){ 
            $lineData = [0,0];
         }
         $lineData[0] += $parts[$this->column1];
         $lineData[1] += $parts[$this->column2];
      }
   }

   public function printResults() {
      if ($this->stats) {
         $this->printStats();
      } else if ($this->ratio) {
         $this->printRatios();
      }
   }

   public function printRatios() {
      foreach($this->lines as $key => $stats) {
         $num = $stats[0];
         $den = $stats[1];
         $diff = $den - $num;
         $pct = round(100 * ($num / $den), 2);
         echo "$key diff:{$diff} $num / $den = $pct%\n";
      }
   }

   public function printStats() {
      foreach($this->lines as $key => $stats) {
         $sum = $stats->sum();
         $min = $stats->min();
         $max = $stats->max();
         $count = $stats->n();
         $avg = $stats->mean();
         $std = $stats->standard_deviation();
         $min = round($min, 3);
         $max = round($max, 3);
         $sum = round($sum, 3);
         $std = round($std, 3);
         $avg = round($avg, 3);
         echo "$key avg:$avg count:$count sum:$sum std:$std min:$min max:$max\n";
      }
   }
}

class StatsStream {
   public function __construct() {
      $this->_min = null;
      $this->_max = null;
      // number of items seen
      $this->_n = 0;
      // running mean
      $this->_mean = 0;
      // running sum of squares deviations from the mean
      $this->_ss = 0;
      // the running 'actual sum'
      $this->_sum = 0;
      $this->writable = true;
   }

   public function write($x) {
      $x = floatval($x);

      $old_n = $this->_n++;

      if ($old_n === 0) {
          $this->_min  = $x;
          $this->_max  = $x;
          $this->_mean = $x;
          $this->_sum  = $x;
      } else {
          if ($x < $this->_min) $this->_min = $x;
          if ($x > $this->_max) $this->_max = $x;
          $xdiff = $x - $this->_mean;
          $this->_ss += $old_n * $xdiff * $xdiff / $this->_n;
          $this->_mean += ($x - $this->_mean) / $this->_n;
          $this->_sum += $x;
      }
   }

   public function n() { return $this->_n; }
   public function min() { return $this->_min; }
   public function max() { return $this->_max; }
   public function sum() { return $this->_sum; }
   public function mean() { return $this->_mean; }

   public function variance() {
       return $this->_ss / $this->_n;
   }

   public function standard_deviation() {
       return sqrt($this->variance());
   }
}

