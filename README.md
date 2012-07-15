OpenROV Wiki
================

The OpenROV wiki is based off of MediaWiki which is a free software open source wiki package written in PHP, originally for use on 
Wikipedia. It is now also used by several other projects of the non-profit Wikimedia Foundation and by many other wikis.

Requirements
------------
* Web server such as Apache or IIS 
 * Local or command line access is needed for running maintenance scripts
* PHP version 5.2.3 or later.
 * PHP 5.3.1 is incompatible with MediaWiki due to a bug. Due to a security issue with PHP it is strongly advised to use PHP 5.2.17+ or PHP 5.3.5+ 
* MySQL 5.0.2 or later

If your PHP is configured as a CGI plug-in rather than an Apache module you may experience problems, as this configuration is not well tested. safe_mode is also not tested and unlikely to work. 

Installation
------------

