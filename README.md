# Input package

[![Latest Stable Version](https://poser.pugx.org/tdt/input/version.png)](https://packagist.org/packages/tdt/input)
[![Build Status](https://travis-ci.org/tdt/input.png?branch=development)](https://travis-ci.org/tdt/input)

This is the Laravel package called "input" and serves as an extract-map-load configuration (EML) as part of the datatank core application (tdt/core). The current instances of the eml stack are focussed on semantifying data. This means that raw data can be transformed into semantic data by providing a mapping file.

Future work exists in extracting data from large files and loading them into a NoSQL store. This endpoint can then be exploited freely, or proxied by the datatank core.

## Configuration with queues

In order to harvest large datasets, jobs will need to be put in a queue so they can be executed asynchronously. One way to do this is to have a beanstalkd service up and running on the server in combination with the artisan queue:listen command.

In order to make sure the artisan listen command, which executes jobs when they enter the beanstalkd queue, keeps running, configure it in supervisord!

Lastly configure the beanstalkd queue in the configuration file of the application. (app/config/queue.php)