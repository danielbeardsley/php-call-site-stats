## CallSiteStats
A php trait that allows easy collection of arbitrary line-level stats about
function calls into a class in a production environment.

Intially created to measure memcache hit / miss ratio on a per-call-site
basis, but can be used to measure anything.

### Usage

Imagine you have a simple class for accessing your cache layer:

    class Cache {
       public function get($key) {
         //...
       }
    }

Add the following to measure hit-rates on a per-call-site basis:

    class Cache {
       use CallSiteStats
       
       // Needed to record call-sites outside of this file
       protected function isExternalCallSite($file) {
          return $file != __FILE__;
       }
       
       public function get($key) {
         //...
         $this->recordCallSite($gets = 1, $cacheHit ? 1 : 0);
       }
    }


Use your class like you normally would:

    // test.php
    $cache = new Cache();
    $value = $cache->get("cachedkey");
    $value = $cache->get("missingkey");

    // The sometime later:
    file_put_contents('cache-gets', $cache->getCallSiteStats(), FILE_APPEND);

See the results:

    $> cat cache-gets
    test.php:3 1 1
    test.php:4 1 0

This information can help you adjust cache times or determine if caching is
even worth it.

### Analysis
Once you've run the code for a bit, you'll end up with a large file that looks
like this:

   /path/to/file.php:21 arg1 arg2
   /path/to/file.php:21 arg1 arg2
   /path/to/other.php:37 arg1 arg2

call-site-stats includes tools to reduce and summarize this data to useful statistics about
each call site.

   # For hit / get ratios specify two columns
   $ summarize -f stats.dat --ratio=1,2
   /path/to/file.php:21 31/90=30%
   /path/to/other.php:21 413/432=96%

   # For in-depth stats specify one column
   $ summarize -f stats.dat --stats=1
   /path/to/file.php:21 min:0 max:13 avg:7.2 std:2.1 90avg:5.4
   /path/to/other.php:21 min:2 max:93 avg:38 std:2.1 90avg:27

