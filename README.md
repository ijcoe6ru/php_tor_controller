#A Tor frontend based on php
 
To use it, put index.php, and src into the root folder of your http document root or a subfolder. Put config.php somewhere and enter its location in line 3 in index.php. Make sure php can write to config.php in order to change the configuration.

All time displayed is UTC.

To prevent too much memory usage, a limit is set for the number of messages to be stored. It is by default 65536.

To prevent too much memory usage, a limit is set for the number of seconds of bandwidth data to be stored. It is by default 601.

Asynchronous events are captured by always having an instance of the php script running.

The current delay between requests to get asynchronous events is 500ms. It can be changed in line 9 in index.php. Having a lower value can lower the resource usage. The interval to check whether another request exists is currently 100ms. Every instance of the  script checks the session for another instance of the script on this interval.

It supports authenticating using the safe cookie method. The clientnonce is a random string. Its length can be changed in line 22 in index.php. In order to generate it, one of the following needs to be available:

* php's random_bytes function
* php's openssl_random_pseudo_bytes function that sets $crypto_strong to true
* /dev/urandom
* CAPICON.Utilities.1 form COM class

It has a mechanism to get the country code of an IP address when tor doesn't give it. It uses the following if available:

* php's geoip_country_code_by_name function
* the operating system's geoiplookup or geoiplookup6 command

The descriptions for Tor are from tor (1) man page. The authors of the decriptions are Roger Dingledine [arma at mit.edu], Nick Mathewson [nickm at alum.mit.edu].
