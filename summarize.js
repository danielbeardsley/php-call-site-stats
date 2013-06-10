var fs = require('fs');
var Stats = require('stream-statistics');
var LineInputStream = require('line-input-stream');
var options = require('optimist').argv;
var grouper = new lineGrouper();

var filename = options.f;
var lineStream = LineInputStream(fs.createReadStream(filename));

if (options.stats) {
   var column = parseInt(options.stats, 10);

   grouper.newGroup = function() {
      return new Stats({store_data: false});
   };

   grouper.eachLine = function(stats, parts) {
      stats.write(parts[column]);
   };

   lineStream.on('end', function() {
      grouper.eachGroup(function(key, stats) {
         console.log(key + 
            " avg:"   + num(stats.mean()) + 
            " count:" + stats.n() + 
            " sum:"   + num(stats.sum()) + 
            " std:"   + num(stats.standard_deviation()) + 
            " min:"   + num(stats.min()) + 
            " max:"   + num(stats.max())
         ); 
      });
   });
}

lineStream.on('line', grouper.push);

/**
 * Groups incoming lines by the string making up the first whitespace
 * delimited column (added via push() function).
 */
function lineGrouper() {
   var groupStats = {};
   var self = this;

   this.push = function (line) {
      var parts = line.split(' ');

      var stats = groupStats[parts[0]] ||
                 (groupStats[parts[0]] = self.newGroup());

      self.eachLine(stats, parts);
   };

   this.eachGroup = function(callback) {
      for (key in groupStats) {
         callback(key, groupStats[key]);
      }
   };
}

function num(x) {
   return Math.round(x*1000) / 1000;
}
