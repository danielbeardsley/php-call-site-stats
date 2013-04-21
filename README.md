## CallSiteStats
A php trait that allows easy collection of arbitrary line-level stats about
function calls into a class in a production environment.

Intially created to measure memcache hit / miss ratio on a per-call-site
basis, but can be used to measure anything.

### Usage

    // Image you have a class for accessing your cache layer
    class Cache {
       public function get($key) {
         //...
       }
    }

    // Add the following to measure hit-rates on a per-call-site basis
    class Cache {
       use CallSiteStats
       
       protected function isExternalCallSite($file) {
          return $file != __FILE__;
       }
       
       public function get($key) {
         //...
         $this->recordCallSite($gets = 1, $cacheHit ? 1 : 0);
       }
    }

    // Use you class like normal
    // test.php
    $cache = new Cache();
    $value = $cache->get("cachedkey");
    $value = $cache->get("missingkey");

    // The sometime later:
    file_put_contents('cache-gets', $cache->getCallSiteStats());

    $> cat cache-gets
    test.php:3 1 1
    test.php:4 1 0

This information can help you adjust cache times or determine if caching is
even worth it.

