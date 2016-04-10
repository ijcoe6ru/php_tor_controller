#A Tor frontend based on php
 
To use it, put index.php, and src into the root folder of your http document root or a subfolder. Put config.php somewhere and enter its location in line 6 in index.php. Make sure php can write to config.php.

All time displayed is UTC.

To prevent too much memory usage, a limit is set for the number of messages to be stored. It is by default 65536.

To prevent too much memory usage, a limit is set for the number of seconds of bandwidth data to be stored. It is by default 601.

It is possible that too many concurrent requests occur in the browser, such as when openning many web pages at the same time. In that case, some requests will be held until the number of concurrent requests fall below the max. We need the requests to occur at exactly the right time to get asynchronous events, so some asynchronous events may be lost then.

The descriptions for Tor are from tor (1) man page. The authors of the decriptions are Roger Dingledine [arma at mit.edu], Nick Mathewson [nickm at alum.mit.edu].
