Get OAuth.php from http://oauth.googlecode.com/svn/code/php/OAuth.php
Get simple-linkedinphp from http://code.google.com/p/simple-linkedinphp/downloads/list
Place the files in a dir named vendor like this:
vendor/
 - OAuth.php
 - linkedin_3.2.0.class.php

! IMPORTANT
In the version I was using you could not set the scope. I've included a patch file you can
use to patch the lib to get a scope. This is REQUIRED for the generator to work!

Getting started:
1) Request your Developer API on Linkedin: https://developer.linkedin.com/
2) Rename config.yml.dist to config.yml and modify the values in the file to reflect your
   received tokens.
3) $ php runMeFirst.php
   and follow the steps.
