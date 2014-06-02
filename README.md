neonblog
========

Experimental indieweb blogging platform

Features
--------

* Microformats2 markup of content
* Post types: "article", "note", and "photo"
* Send and receive webmentions
* Micropub endpoint
* No SQL database -- posts are stored in html with microformats2

Installation
------------

Requirements:

* PHP >= 5.3
* mod_rewrite
* PHP-Mf2

All configurable parameters are in `config.json`. It relies on IndieAuth for authentication, so you must set at least one IndieAuth compatible rel-me link. Posts are stored as html fragments in `p`. Posting order is defined by lexicographic
order of the filenames. This may change in the future to reflect the `published` date parsed from the files.

Received webmentions are queued in the file `webmentions.json`. To process the queue, manually run
`php -f processqueue.php`, or run it periodically from cron.

TODO
----

* ~~License~~
* ~~Posting UI~~
* ~~Micropub~~
* ~~Sending webmentions~~
* Reply contexts
* POSSE
  * twitter
* https support
* post formatting (linebreaks, url auto-linking)
* comment approval UI
* caching

License
-------

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
