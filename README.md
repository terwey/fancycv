# FancyCV
## Setup
All dependencies can be installed via Composer:

    php composer.phar install

##Getting started:
1. Request your Developer API on Linkedin: https://developer.linkedin.com/
2. Rename config.yml.dist to config.yml and modify the values in the file to reflect your
   received tokens.

Usage:
```
$ chmod +x fancycv.php
$ ./fancycv.php init
$ ./fancycv.php auth
$ ./fancycv.php generate
```
