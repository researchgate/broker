broker
======

broker is a full repository proxy for [composer](https://github.com/composer/composer). It takes
a composer file, downloads all requirements and all dependencies, and then publishes a new
repository with all these packages. Instead of [packagist](https://github.com/composer/packagist)
or [satis](https://github.com/composer/satis), all packages, including dist and source filles will
be served directly by broker.

Installation
------------

Clone broker in a directory that is accessable by your webserver:

    git clone git://github.com/researchgate/broker.git

Download composer inter broker's root directory:

    cd broker
    wget http://getcomposer.org/composer.phar

Install all dependecies of broker:

    php composer.phar install

Commands
--------

Currently broker has two cli commands available

* #### broker:add

  With broker:add you can add a new repository based on a composer json file

        php broker.php broker:add repository_name path/to/composer.json


* #### broker:remove

  With broker:remove you can remove an existing repository

        php broker.php broker:remove repository_name


Web Interface
-------------

Broker also comes with a small web interface, that shows you all existing repositories and
detailed information about the packages in it.

Using a broker repository in your project
-----------------------------------------

Just add the following repository reference to your project's composer.json file

    "repositories":{
        "packagist": false,
        "repository_name": {
            "composer": {
                "url": "http://url/to/broker/repositories/repository_name"
            }
        }
    },

