neonblog
========

Experimental indieweb blogging platform

Features
--------

* Post articles, notes, photos, replies, likes, reposts
* Send and receive webmentions
* RelMeAuth based login
* Micropub endpoint
* POSSE and receive backfeeds from Twitter and Facebook (via Bridgy)
* Search

Installation
------------

Requirements:

* PHP >= 5.4 (json, curl, openssl, imagick)
* Apache mod_rewrite
* write access to the filesystem (CGI/FCGI should work, mod_php may be problematic)

All configurable parameters are in `config.json`. At a minimum, you will need to set your site URL and title, your name and photo, and at least one IndieAuth compatible rel-me link.

Neonblog stores posts directly on the filesystem and needs write access to its directory. If you add/remove posts directly on the filesystem, you will need to reindex by running `php -f scripts/reindex.php`. If you change the templates, you will need to regenerate the static pages by running `php -f scripts/regenerate.php`.

Received webmentions can be accepted on the inbox page. To accept everything automatically, run `php -f scripts/processqueue.php` as a cron job.

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
* autolink photos, soundcloud, youtube players in notes
* ~~likes/reposts~~
* https support
* ~~comment approval UI~~
* ~~search~~

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
