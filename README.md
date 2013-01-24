# FancyCV
## Setup
* ### Composer
First pull in the deps from Composer as far as possible.
```$ chmod +x composer.phar
$ ./composer.phar install```

* ### OAuth
Get OAuth.php from http://oauth.googlecode.com/svn/code/php/OAuth.php

* ### Simple-LinkedInPHP
Get simple-linkedinphp from http://code.google.com/p/simple-linkedinphp/downloads/list
#### Patch linkedin_3.2.0.class.php
In the version I was using you could not set the scope. I've included a patch file you can
use to patch the lib to get a scope. This is REQUIRED for the generator to work! The file is called ```linkedin.patch```

Place the files in the dir composer created
```vendor/
 - OAuth.php
 - linkedin_3.2.0.class.php```

Modify vendor/autoload.php and add:
```require_once __DIR__ . '/linkedin_3.2.0.class.php';```


##Getting started:
1. Request your Developer API on Linkedin: https://developer.linkedin.com/
2. Rename config.yml.dist to config.yml and modify the values in the file to reflect your
   received tokens.
3. 	```$ chmod +x fancycv.php
$ ./fancycv.php init
$ ./fancycv.php auth
$ ./fancycv.php generate```
