neonblog
========

Experimental indieweb blogging platform

Features
--------

* Post articles, notes, and photos
* Send and receive replies via webmention
* POSSE and receive backfeeds from twitter, facebook (via bridgy)
* Micropub endpoint
* Responsive bootstrap theme
* No SQL database -- content is stored in html

Installation
------------

Requirements:

* PHP >= 5.3
* mod_rewrite
* PHP-Mf2

All configurable parameters are in `config.json`. At a minimum, you will need to set your site URL and title, your name and photo, and at least one IndieAuth compatible rel-me link.

Neonblog stores posts directly on the filesystem and needs write access to its directory. If you add/remove posts directly on the filesystem, you will need to regenerate the index by running `php -f regenerate.php`.

Received webmentions can be accepted on the inbox page. To accept everything automatically, run `php -f processqueue.php` as a cron job.

TODO
----

* ~~License~~
* ~~Posting UI~~
* ~~Micropub~~
* ~~Sending webmentions~~
* ~~Reply contexts~~
* ~~POSSE~~
  * ~~twitter~~
  * ~~facebook~~
  * soundcloud
* https support
* ~~comment approval UI~~
* ~~search~~
* feed reader

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
