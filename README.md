## CallSiteStats
A php trait that allows easy collection of arbitrary line-level stats about
function calls into a class in a production environment.

Intially created to measure memcache hit / miss ratio on a per-call-site
basis, but can be used to measure anything.
