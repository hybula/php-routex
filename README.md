# Routex
Simple and fast URL router powered by regex with predefined patterns.

### Introduction
This router has been developed due to the need of a very simple and lightweight router. Unlike routers used in (micro-)frameworks, this class is just one file. It offers powerful routing using PCRE compliant regex, or simply use the built in placeholders.

### Requirements
- PHP 7.4+
- URL rewriting (see .htaccess)

### Usage
```php
<?php

require_once __DIR__.'/Hybula/Routex/Routex.php';
use Hybula\Routex\Routex;

// Override the default 404, you may set a different status code too. If not set run() will return false and a 404 status code;
Routex::error(404, function(){
    echo '404';
});

// Enable basic debugging by printing pattern and routes;
Routex::debug(true);

// Change the base URL, use this if you are routing inside a directory instead of root;
Routex::base('/v1/');

// Load build in patterns and set optional custom patterns to be used in routing URLs later;
Routex::patterns([
    ':hostname' => '((?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63})'
]);

// Load external files for routing, so you can create seperate files per route;
Routex::load(__DIR__.'/routes');

// Example how to route the index;
Routex::get('/', function(){
    echo 'Hello world!';
});

// Example on how to use PCRE regex to route and capture params;
Routex::get('blog/(\w+)/(\d+)', function($category, $id){
    echo 'The category is: '.$category.' and the ID is: '. $id;
});

// Use the custom pattern defined above for routing;
Routex::get('/endpoint/:hostname', function($hostname){
    echo $hostname;
});

// Use predefined patterns using placeholders;
Routex::get('/endpoint/:number/another', function($number){
    echo $number;
});

// Start routing, this will return false if no route is matched and if there is no error() defined. Returns null if route is found and Closure is executed;
Routex::run();
```


### Placeholders
Routex currently support the following placeholders, these are built in patterns and then converted to regex.

- :domain = Matches any valid domain
- :number = Matches any number (digits)
- :word = Matches any word (letter, number, underscore)

### Contribute
Contributions are welcome in a form of a pull request (PR).

### License
Mozilla Public License Version 2.0
