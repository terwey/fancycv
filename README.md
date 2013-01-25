# FancyCV
## Setup
All script dependencies can be installed via Composer:

    php composer.phar install

### LaTeX
LaTeX dependencies have to be installed accordingly to your distribution. The script has *NOT* been tested on Windows!
#### Gentoo
For Gentoo you can install the following:

    emerge -av app-text/texlive

#### Debian
Note I did not install this myself, this is what I found on: http://wiki.debian.org/Latex

	apt-get install texlive

##Getting started:
1. Request your Developer API on Linkedin: https://developer.linkedin.com/
2. Rename config.yml.dist to config.yml and modify the values in the file to reflect your
   received tokens.

Usage:

	$ chmod +x fancycv.php
	$ ./fancycv.php init
	$ ./fancycv.php auth
	$ ./fancycv.php fetch
	
	# see ./fancycv.php help generate for options
	# for the defaults you NEED pdflatex!
	# ./fancycv.php generate defaults to:
	# ./fancycv.php generate --format="tex" --output="generated" --directory="output"
	# which can also be called like this:
	$ ./fancycv.php generate
	
	# generates in HTML format
	$ ./fancycv.php generate -f html
