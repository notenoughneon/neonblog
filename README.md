neonblog
========

Experimental indieweb blogging platform

Features
--------

* Microformats2 markup of content
* Supports post types: "article" and "note"
* Receive and display webmentions
* No SQL database -- posts are stored in html with microformats2

Installation
------------

Requirements:

* PHP >= 5.3
* mod_rewrite
* PHP-Mf2

All configurable parameters are in `$config` in `common.php`. The posting UI is not yet implemented,
so posts are created by dropping html files in `p\`. Posting order is defined by lexicographic
order of the filenames. This may change in the future to reflect the `published` date parsed from the files.

Received webmentions are queued in the file `webmentions.txt`. To process the queue, manually run
`php -f processqueue.php`, or run it periodically from cron.

TODO
----

* License
* Posting UI
* Micropub
* Sending webmentions
* Reply contexts
* POSSE
